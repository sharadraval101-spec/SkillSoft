<?php

namespace App\Listeners;

use App\Events\UserNotificationTriggered;
use App\Models\User;
use App\Services\NotificationDeliveryService;

class SendFallbackNotificationListener
{
    public function __construct(private readonly NotificationDeliveryService $deliveryService)
    {
    }

    public function handle(UserNotificationTriggered $event): void
    {
        $user = User::query()->find($event->userId);
        if (!$user) {
            return;
        }

        $this->deliveryService->deliver(
            $user,
            $event->title,
            $event->message,
            $event->data,
            $event->sendEmailFallback,
            $event->sendSms,
            $event->sendWhatsapp,
        );
    }
}
