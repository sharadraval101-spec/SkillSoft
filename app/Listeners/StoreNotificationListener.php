<?php

namespace App\Listeners;

use App\Events\UserNotificationTriggered;
use App\Models\Notification;

class StoreNotificationListener
{
    public function handle(UserNotificationTriggered $event): void
    {
        Notification::query()->create([
            'user_id' => $event->userId,
            'type' => $event->type,
            'title' => $event->title,
            'message' => $event->message,
            'data' => $event->data,
            'read_at' => null,
        ]);
    }
}
