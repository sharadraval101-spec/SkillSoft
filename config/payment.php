<?php

return [
    'currency' => env('PAYMENT_DEFAULT_CURRENCY', 'INR'),
    'platform_fee_percent' => (float) env('PAYMENT_PLATFORM_FEE_PERCENT', 10),
    'allow_cash' => filter_var(env('PAYMENT_ALLOW_CASH', false), FILTER_VALIDATE_BOOL),
    'online' => [
        // Useful for environments without webhook/capture handling.
        'mark_paid_immediately' => filter_var(env('PAYMENT_ONLINE_MARK_PAID_IMMEDIATELY', false), FILTER_VALIDATE_BOOL),
    ],
    'refund' => [
        'customer_cutoff_hours_before' => (int) env('PAYMENT_REFUND_CUSTOMER_CUTOFF_HOURS_BEFORE', 2),
        'customer_advance_percent' => (float) env('PAYMENT_REFUND_CUSTOMER_ADVANCE_PERCENT', 50),
        'customer_late_percent' => (float) env('PAYMENT_REFUND_CUSTOMER_LATE_PERCENT', 0),
        'provider_cancellation_percent' => (float) env('PAYMENT_REFUND_PROVIDER_CANCELLATION_PERCENT', 100),
    ],
];
