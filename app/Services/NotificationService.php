<?php

namespace App\Services;

use App\Events\UserNotificationTriggered;
use App\Models\User;

class NotificationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyUser(
        User|int $user,
        string $type,
        string $title,
        ?string $message = null,
        array $data = [],
        bool $sendEmailFallback = true,
        bool $sendSms = false,
        bool $sendWhatsapp = false,
    ): void {
        $userId = $user instanceof User ? $user->id : $user;

        event(new UserNotificationTriggered(
            userId: (int) $userId,
            type: $type,
            title: $title,
            message: $message,
            data: $data,
            sendEmailFallback: $sendEmailFallback,
            sendSms: $sendSms,
            sendWhatsapp: $sendWhatsapp
        ));
    }
}
