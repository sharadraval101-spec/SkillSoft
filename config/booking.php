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
        // Maximum number of active upcoming bookings allowed per customer.
        'max_active_upcoming' => (int) env('BOOKING_MAX_ACTIVE_UPCOMING', 3),
    ],
    'auto_reschedule' => [
        'search_days' => (int) env('BOOKING_AUTO_RESCHEDULE_SEARCH_DAYS', 45),
    ],
];
