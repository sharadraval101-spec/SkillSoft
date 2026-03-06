<?php

return [
    'email_fallback' => filter_var(env('NOTIFICATIONS_EMAIL_FALLBACK', true), FILTER_VALIDATE_BOOL),
    'sms' => [
        'enabled' => filter_var(env('NOTIFICATIONS_SMS_ENABLED', false), FILTER_VALIDATE_BOOL),
        'endpoint' => env('NOTIFICATIONS_SMS_ENDPOINT'),
        'token' => env('NOTIFICATIONS_SMS_TOKEN'),
        'from' => env('NOTIFICATIONS_SMS_FROM'),
    ],
    'whatsapp' => [
        'enabled' => filter_var(env('NOTIFICATIONS_WHATSAPP_ENABLED', false), FILTER_VALIDATE_BOOL),
        'endpoint' => env('NOTIFICATIONS_WHATSAPP_ENDPOINT'),
        'token' => env('NOTIFICATIONS_WHATSAPP_TOKEN'),
        'from' => env('NOTIFICATIONS_WHATSAPP_FROM'),
    ],
];
