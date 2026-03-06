<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function online(Request $request): JsonResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $data = $request->validate([
            'booking_id' => 'required|uuid|exists:bookings,id',
            'gateway' => 'required|in:razorpay,stripe,paypal',
            'payment_mode' => 'nullable|in:prepaid',
        ]);

        $booking = Booking::query()
            ->where('customer_id', $customer->id)
            ->findOrFail($data['booking_id']);

        $payment = $this->paymentService->payOnline(
            $customer,
            $booking,
            $data['gateway'],
            $data['payment_mode'] ?? Payment::MODE_PREPAID
        );

        return response()->json([
            'message' => 'Online payment initiated successfully.',
            'data' => $this->transformPayment($payment),
        ], 201);
    }

    public function cash(Request $request): JsonResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $data = $request->validate([
            'booking_id' => 'required|uuid|exists:bookings,id',
            'payment_mode' => 'required|in:prepaid,postpaid',
        ]);

        $booking = Booking::query()
            ->where('customer_id', $customer->id)
            ->findOrFail($data['booking_id']);

        $payment = $this->paymentService->payCash($customer, $booking, $data['payment_mode']);

        return response()->json([
            'message' => 'Cash payment recorded successfully.',
            'data' => $this->transformPayment($payment),
        ], 201);
    }

    public function refund(Request $request): JsonResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $data = $request->validate([
            'payment_id' => 'required|uuid|exists:payments,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $payment = Payment::query()
            ->where('customer_id', $customer->id)
            ->findOrFail($data['payment_id']);

        $payment = $this->paymentService->refund($customer, $payment, $data['reason'] ?? null);

        return response()->json([
            'message' => 'Refund processed successfully.',
            'data' => $this->transformPayment($payment),
        ]);
    }

    private function transformPayment(Payment $payment): array
    {
        $payment->loadMissing(['booking:id,booking_number,scheduled_at,status', 'provider:id,name,email']);

        return [
            'id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'booking_number' => $payment->booking?->booking_number,
            'provider_id' => $payment->provider_id,
            'provider_name' => $payment->provider?->name,
            'gateway' => $payment->gateway,
            'method' => $payment->method,
            'payment_mode' => $payment->payment_mode,
            'gateway_reference' => $payment->gateway_reference,
            'amount' => $payment->amount,
            'refunded_amount' => $payment->refunded_amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
            'refunded_at' => $payment->refunded_at,
            'meta' => $payment->meta,
            'created_at' => $payment->created_at,
        ];
    }
}
