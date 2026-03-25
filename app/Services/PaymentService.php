<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Payment;
use App\Models\ProviderPayout;
use App\Models\Slot;
use App\Models\User;
use App\Services\Payments\OnlineGatewayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly OnlineGatewayService $gatewayService,
        private readonly NotificationService $notificationService
    )
    {
    }

    public function payOnline(User $customer, Booking $booking, string $gateway, string $paymentMode = Payment::MODE_PREPAID): Payment
    {
        return DB::transaction(function () use ($customer, $booking, $gateway, $paymentMode): Payment {
            $booking = $this->lockOwnedBooking($booking, $customer);

            $this->validatePaymentRules($booking, Payment::METHOD_ONLINE, $paymentMode);

            $amount = $this->bookingAmount($booking);
            $currency = (string) config('payment.currency', 'INR');

            try {
                $gatewayResult = $this->gatewayService->createOrder($gateway, $amount, $currency, [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'customer_id' => $customer->id,
                ]);
            } catch (ValidationException $exception) {
                if ($paymentMode === Payment::MODE_PREPAID) {
                    $this->cancelBookingForFailedPrepaidPayment($booking);
                }

                throw $exception;
            }

            $gatewayStatus = strtolower((string) ($gatewayResult['status'] ?? Payment::STATUS_PENDING));
            $isGatewayFailure = in_array($gatewayStatus, [Payment::STATUS_FAILED, 'cancelled', 'canceled'], true);
            $markPaidImmediately = (bool) config('payment.online.mark_paid_immediately', true);
            $isPaid = $markPaidImmediately || (bool) ($gatewayResult['simulated'] ?? false);
            $paymentStatus = $isPaid
                ? Payment::STATUS_PAID
                : ($isGatewayFailure ? Payment::STATUS_FAILED : Payment::STATUS_PENDING);

            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'customer_id' => $customer->id,
                'provider_id' => $booking->provider_id,
                'gateway' => $gateway,
                'method' => Payment::METHOD_ONLINE,
                'payment_mode' => $paymentMode,
                'gateway_reference' => $gatewayResult['reference'] ?? null,
                'amount' => $amount,
                'refunded_amount' => 0,
                'currency' => strtoupper($currency),
                'status' => $paymentStatus,
                'paid_at' => $isPaid ? now() : null,
                'meta' => [
                    'gateway_payload' => $gatewayResult['payload'] ?? [],
                    'simulated' => (bool) ($gatewayResult['simulated'] ?? false),
                ],
            ]);

            if ($isPaid) {
                $this->markBookingConfirmedAfterSuccessfulPayment($booking);
                $this->syncCommissionAndPayout($booking, $payment, $amount, 0);
            } elseif ($isGatewayFailure && $paymentMode === Payment::MODE_PREPAID) {
                $this->cancelBookingForFailedPrepaidPayment($booking);
            }

            $freshPayment = $payment->fresh(['booking.service', 'booking.serviceVariant']);

            $eventType = $isPaid
                ? 'payment.online.paid'
                : ($paymentStatus === Payment::STATUS_FAILED ? 'payment.online.failed' : 'payment.online.initiated');
            $eventTitle = $isPaid
                ? 'Online Payment Successful'
                : ($paymentStatus === Payment::STATUS_FAILED ? 'Online Payment Failed' : 'Online Payment Initiated');

            $this->notifyPaymentUpdate($customer, $booking, $freshPayment, $eventType, $eventTitle);

            return $freshPayment;
        });
    }

    public function payCash(User $customer, Booking $booking, string $paymentMode = Payment::MODE_POSTPAID): Payment
    {
        return DB::transaction(function () use ($customer, $booking, $paymentMode): Payment {
            $booking = $this->lockOwnedBooking($booking, $customer);

            $this->validatePaymentRules($booking, Payment::METHOD_CASH, $paymentMode);

            $amount = $this->bookingAmount($booking);
            $currency = (string) config('payment.currency', 'INR');
            $isPaid = $paymentMode === Payment::MODE_PREPAID;

            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'customer_id' => $customer->id,
                'provider_id' => $booking->provider_id,
                'gateway' => Payment::GATEWAY_CASH,
                'method' => Payment::METHOD_CASH,
                'payment_mode' => $paymentMode,
                'gateway_reference' => null,
                'amount' => $amount,
                'refunded_amount' => 0,
                'currency' => strtoupper($currency),
                'status' => $isPaid ? Payment::STATUS_PAID : Payment::STATUS_PENDING,
                'paid_at' => $isPaid ? now() : null,
                'meta' => ['source' => 'cash'],
            ]);

            if ($isPaid) {
                $this->markBookingConfirmedAfterSuccessfulPayment($booking);
                $this->syncCommissionAndPayout($booking, $payment, $amount, 0);
            }

            $freshPayment = $payment->fresh(['booking.service', 'booking.serviceVariant']);

            $this->notifyPaymentUpdate($customer, $booking, $freshPayment, 'payment.cash.recorded', 'Cash Payment Recorded');

            return $freshPayment;
        });
    }

    public function refund(User $customer, Payment $payment, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($customer, $payment, $reason): Payment {
            $payment = Payment::query()
                ->with(['booking.service', 'booking.serviceVariant'])
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ((int) $payment->customer_id !== (int) $customer->id) {
                abort(403);
            }

            if ($payment->status !== Payment::STATUS_PAID) {
                throw ValidationException::withMessages([
                    'payment' => 'Only paid payments can be refunded.',
                ]);
            }

            $booking = $payment->booking;
            if (!$booking || $booking->status !== Booking::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'payment' => 'Refund is allowed only for cancelled bookings.',
                ]);
            }

            $refundPercent = $this->refundPercentForBooking($booking);
            $remaining = max(0, (float) $payment->amount - (float) $payment->refunded_amount);
            $refundAmount = round($remaining * ($refundPercent / 100), 2);

            if ($refundAmount <= 0) {
                throw ValidationException::withMessages([
                    'payment' => 'No refundable amount available for this payment.',
                ]);
            }

            if ($payment->method === Payment::METHOD_ONLINE && $payment->gateway !== Payment::GATEWAY_CASH) {
                $this->gatewayService->refund(
                    $payment->gateway,
                    (string) $payment->gateway_reference,
                    $refundAmount,
                    $payment->currency
                );
            }

            $newRefunded = round((float) $payment->refunded_amount + $refundAmount, 2);
            $fullyRefunded = $newRefunded >= ((float) $payment->amount - 0.009);

            $payment->update([
                'refunded_amount' => $newRefunded,
                'status' => $fullyRefunded ? Payment::STATUS_REFUNDED : Payment::STATUS_PAID,
                'refunded_at' => now(),
                'refund_reason' => $reason,
                'meta' => array_merge($payment->meta ?? [], [
                    'refund' => [
                        'last_refund_amount' => $refundAmount,
                        'refund_percent' => $refundPercent,
                        'updated_at' => now()->toIso8601String(),
                    ],
                ]),
            ]);

            $netCollected = max(0, (float) $payment->amount - $newRefunded);
            $this->syncCommissionAndPayout($booking, $payment->fresh(), $netCollected, $newRefunded);

            $freshPayment = $payment->fresh(['booking.service', 'booking.serviceVariant']);

            $this->notifyPaymentUpdate($customer, $booking, $freshPayment, 'payment.refunded', 'Payment Refunded');

            return $freshPayment;
        });
    }

    public function canRefund(Payment $payment): bool
    {
        return $payment->status === Payment::STATUS_PAID
            && (float) $payment->amount > (float) $payment->refunded_amount
            && $payment->booking
            && $payment->booking->status === Booking::STATUS_CANCELLED;
    }

    private function syncCommissionAndPayout(Booking $booking, Payment $payment, float $netCollected, float $refunded): void
    {
        $provider = User::query()->with('providerProfile')->find($booking->provider_id);
        $feePercent = $provider?->providerProfile?->commission_rate
            ? (float) $provider->providerProfile->commission_rate
            : (float) config('payment.platform_fee_percent', 10);
        $feePercent = max(0, min(100, $feePercent));

        $platformFeeAmount = round($netCollected * ($feePercent / 100), 2);
        $providerEarning = round(max(0, $netCollected - $platformFeeAmount), 2);

        $commission = Commission::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'provider_id' => $booking->provider_id,
                'platform_fee_percent' => $feePercent,
                'platform_fee_amount' => $platformFeeAmount,
                'provider_earning' => $providerEarning,
                'currency' => $payment->currency,
                'status' => $providerEarning > 0 ? 'pending' : 'settled',
                'settled_at' => $providerEarning > 0 ? null : now(),
            ]
        );

        ProviderPayout::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'commission_id' => $commission->id,
                'provider_id' => $booking->provider_id,
                'gross_amount' => $netCollected,
                'platform_fee_amount' => $platformFeeAmount,
                'net_amount' => $providerEarning,
                'currency' => $payment->currency,
                'status' => $providerEarning > 0
                    ? ProviderPayout::STATUS_PENDING
                    : ProviderPayout::STATUS_REVERSED,
                'gateway' => $payment->gateway,
                'gateway_reference' => $payment->gateway_reference,
                'meta' => [
                    'payment_id' => $payment->id,
                    'refunded_amount' => $refunded,
                ],
            ]
        );
    }

    private function validatePaymentRules(Booking $booking, string $method, string $paymentMode): void
    {
        if ($method === Payment::METHOD_ONLINE && $paymentMode !== Payment::MODE_PREPAID) {
            throw ValidationException::withMessages([
                'payment_mode' => 'Online payments are allowed only as prepaid.',
            ]);
        }

        if ($paymentMode === Payment::MODE_PREPAID) {
            if (!in_array($booking->status, Booking::activeStatuses(), true)) {
                throw ValidationException::withMessages([
                    'booking' => 'Prepaid payment is allowed only for pending or accepted bookings.',
                ]);
            }

            if ($booking->scheduled_at && $booking->scheduled_at->lte(now())) {
                throw ValidationException::withMessages([
                    'booking' => 'Prepaid payment must be completed before appointment time.',
                ]);
            }
        }

        if ($paymentMode === Payment::MODE_POSTPAID) {
            if ($method !== Payment::METHOD_CASH) {
                throw ValidationException::withMessages([
                    'payment_mode' => 'Postpaid is supported only for cash flow.',
                ]);
            }

            if (!in_array($booking->status, [Booking::STATUS_ACCEPTED, Booking::STATUS_COMPLETED], true)) {
                throw ValidationException::withMessages([
                    'booking' => 'Postpaid cash can be recorded only after booking is accepted/completed.',
                ]);
            }
        }

        $hasPaidPayment = Payment::query()
            ->where('booking_id', $booking->id)
            ->whereIn('status', [Payment::STATUS_PAID, Payment::STATUS_REFUNDED])
            ->exists();

        if ($hasPaidPayment && $paymentMode === Payment::MODE_PREPAID) {
            throw ValidationException::withMessages([
                'booking' => 'This booking already has a completed prepaid payment.',
            ]);
        }
    }

    private function bookingAmount(Booking $booking): float
    {
        $booking->loadMissing(['service', 'serviceVariant']);

        $amount = $booking->serviceVariant?->price ?? $booking->service?->base_price ?? 0;
        $amount = round((float) $amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'booking' => 'Unable to determine payable amount for this booking.',
            ]);
        }

        return $amount;
    }

    private function refundPercentForBooking(Booking $booking): float
    {
        $cancelledAt = $booking->cancelled_at ?? now();
        $hoursBefore = ($booking->scheduled_at->timestamp - $cancelledAt->timestamp) / 3600;

        $fullHours = max(0, (int) config('payment.refund.full_refund_hours_before', 24));
        if ($hoursBefore >= $fullHours) {
            return 100.0;
        }

        $partial = (float) config('payment.refund.partial_refund_percent', 50);
        return max(0, min(100, $partial));
    }

    private function lockOwnedBooking(Booking $booking, User $customer): Booking
    {
        $locked = Booking::query()
            ->with(['service', 'serviceVariant'])
            ->lockForUpdate()
            ->findOrFail($booking->id);

        if ((int) $locked->customer_id !== (int) $customer->id) {
            abort(403);
        }

        return $locked;
    }

    private function notifyPaymentUpdate(
        User $customer,
        Booking $booking,
        Payment $payment,
        string $type,
        string $title
    ): void {
        $message = 'Booking '.$booking->booking_number.' • '.strtoupper($payment->gateway).' • '.strtoupper($payment->status);
        $payload = [
            'booking_id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
        ];

        $this->notificationService->notifyUser(
            $customer,
            $type,
            $title,
            $message,
            $payload,
            sendEmailFallback: true,
            sendSms: true,
            sendWhatsapp: true
        );

        if ($booking->provider_id) {
            $this->notificationService->notifyUser(
                (int) $booking->provider_id,
                $type.'.provider',
                $title,
                'Customer '.$customer->name.' updated payment for booking '.$booking->booking_number.'.',
                $payload,
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );
        }
    }

    private function markBookingConfirmedAfterSuccessfulPayment(Booking $booking): void
    {
        if (in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_ACCEPTED], true)) {
            $booking->update([
                'status' => Booking::STATUS_ACCEPTED,
                'cancelled_at' => null,
            ]);
        }
    }

    private function cancelBookingForFailedPrepaidPayment(Booking $booking): void
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            return;
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'notes' => $this->appendSystemNote(
                $booking->notes,
                '[System] Booking auto-cancelled due to failed payment.'
            ),
        ]);

        /** @var Slot|null $slot */
        $slot = Slot::query()->lockForUpdate()->find($booking->slot_id);
        if (!$slot) {
            return;
        }

        $hasActiveBooking = Booking::query()
            ->where('slot_id', $slot->id)
            ->whereIn('status', Booking::slotBlockingStatuses())
            ->exists();

        $slot->update([
            'is_available' => !$hasActiveBooking && $slot->start_at->gte(now()),
        ]);
    }

    private function appendSystemNote(?string $existing, string $line): string
    {
        $existing = (string) $existing;
        return $existing === '' ? $line : $existing.PHP_EOL.$line;
    }
}
