<?php

return [
    'window' => [
        // Customer must book at least N hours before slot start.
        'min_hours_before' => (int) env('BOOKING_MIN_HOURS_BEFORE', 2),
        // Customer can book up to N days in advance.
        'max_days_ahead' => (int) env('BOOKING_MAX_DAYS_AHEAD', 45),
    ],
    'rules' => [
        // Reschedule is blocked if slot starts within N hours.
        'reschedule_cutoff_hours' => (int) env('BOOKING_RESCHEDULE_CUTOFF_HOURS', 12),
        // Cancel is blocked if slot starts within N hours.
        'cancel_cutoff_hours' => (int) env('BOOKING_CANCEL_CUTOFF_HOURS', 2),
    ],
];
