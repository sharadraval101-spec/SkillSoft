<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationTriggered
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $message = null,
        public readonly array $data = [],
        public readonly bool $sendEmailFallback = true,
        public readonly bool $sendSms = false,
        public readonly bool $sendWhatsapp = false,
    ) {
    }
}
