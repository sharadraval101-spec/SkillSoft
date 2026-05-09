<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;

class CancellationPolicyService
{
    /**
     * @return array{cancelled_by:string, refund_percent:float, refundable_amount:float, cancellation_fee_amount:float, total_amount:float, hours_before:float}
     */
    public function evaluate(Booking $booking, Payment $payment): array
    {
        $cancelledBy = (string) ($booking->cancelled_by ?: Booking::CANCELLED_BY_CUSTOMER);
        $cancelledAt = $booking->cancelled_at ?? now();
        $scheduledAt = $booking->scheduled_at ?? now();
        $hoursBefore = ($scheduledAt->timestamp - $cancelledAt->timestamp) / 3600;

        if (in_array($cancelledBy, [Booking::CANCELLED_BY_PROVIDER, Booking::CANCELLED_BY_ADMIN], true)) {
            $refundPercent = (float) config('payment.refund.provider_cancellation_percent', 100);
        } else {
            $cutoffHours = max(0, (int) config('payment.refund.customer_cutoff_hours_before', 2));
            $advancePercent = (float) config('payment.refund.customer_advance_percent', 50);
            $latePercent = (float) config('payment.refund.customer_late_percent', 0);

            $refundPercent = $hoursBefore > $cutoffHours ? $advancePercent : $latePercent;
        }

        $refundPercent = max(0, min(100, $refundPercent));
        $totalAmount = round((float) $payment->amount, 2);
        $refundableAmount = round($totalAmount * ($refundPercent / 100), 2);
        $cancellationFeeAmount = round(max(0, $totalAmount - $refundableAmount), 2);

        return [
            'cancelled_by' => $cancelledBy,
            'refund_percent' => $refundPercent,
            'refundable_amount' => $refundableAmount,
            'cancellation_fee_amount' => $cancellationFeeAmount,
            'total_amount' => $totalAmount,
            'hours_before' => round($hoursBefore, 2),
        ];
    }
}
