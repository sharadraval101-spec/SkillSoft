<?php

namespace App\Services;

use App\Mail\UserNotificationFallbackMail;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationDeliveryService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function deliver(
        User $user,
        string $title,
        ?string $message = null,
        array $data = [],
        bool $sendEmail = true,
        bool $sendSms = false,
        bool $sendWhatsapp = false,
    ): void {
        if ($sendEmail && (bool) config('notifications.email_fallback', true)) {
            try {
                Mail::to($user->email)->send(new UserNotificationFallbackMail($user, $title, $message, $data));
            } catch (\Throwable $e) {
                Log::warning('Email notification fallback failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($sendSms && (bool) config('notifications.sms.enabled', false)) {
            $this->sendSms($user, $title, $message);
        }

        if ($sendWhatsapp && (bool) config('notifications.whatsapp.enabled', false)) {
            $this->sendWhatsapp($user, $title, $message);
        }
    }

    private function sendSms(User $user, string $title, ?string $message): void
    {
        $endpoint = (string) config('notifications.sms.endpoint');
        $recipient = (string) data_get($user, 'phone', '');
        if ($endpoint === '' || trim($recipient) === '') {
            return;
        }

        $payload = [
            'to' => $recipient,
            'from' => config('notifications.sms.from'),
            'message' => trim($title.' - '.($message ?? '')),
        ];

        try {
            Http::withToken((string) config('notifications.sms.token'))
                ->post($endpoint, $payload)
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('SMS notification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWhatsapp(User $user, string $title, ?string $message): void
    {
        $endpoint = (string) config('notifications.whatsapp.endpoint');
        $recipient = (string) data_get($user, 'phone', '');
        if ($endpoint === '' || trim($recipient) === '') {
            return;
        }

        $payload = [
            'to' => $recipient,
            'from' => config('notifications.whatsapp.from'),
            'message' => trim($title.' - '.($message ?? '')),
        ];

        try {
            Http::withToken((string) config('notifications.whatsapp.token'))
                ->post($endpoint, $payload)
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
