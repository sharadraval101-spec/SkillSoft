<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBookingCancellationRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $bookingId,
        public readonly ?string $reason = null
    ) {
    }

    public function handle(PaymentService $paymentService): void
    {
        $booking = Booking::query()->find($this->bookingId);
        if (!$booking) {
            return;
        }

        $payments = Payment::query()
            ->where('booking_id', $booking->id)
            ->whereIn('status', [
                Payment::STATUS_PAID,
                Payment::STATUS_REFUNDED,
            ])
            ->get();

        foreach ($payments as $payment) {
            $paymentService->applyCancellationPolicyRefund($payment, $this->reason);
        }
    }
}
