<?php

return [
    'currency' => env('PAYMENT_DEFAULT_CURRENCY', 'INR'),
    'platform_fee_percent' => (float) env('PAYMENT_PLATFORM_FEE_PERCENT', 10),
    'online' => [
        // Useful for environments without webhook/capture handling.
        'mark_paid_immediately' => filter_var(env('PAYMENT_ONLINE_MARK_PAID_IMMEDIATELY', false), FILTER_VALIDATE_BOOL),
    ],
    'refund' => [
        // Full refund if cancelled N hours before scheduled time.
        'full_refund_hours_before' => (int) env('PAYMENT_REFUND_FULL_HOURS_BEFORE', 24),
        // Partial refund percentage if full refund window is missed.
        'partial_refund_percent' => (float) env('PAYMENT_REFUND_PARTIAL_PERCENT', 50),
    ],
];
