<?php

namespace App\Mail;

use App\Models\ProviderRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderRequestApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ProviderRequest $providerRequest,
        public readonly User $user,
        public readonly string $plainPassword,
        public readonly string $loginUrl
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your provider account has been approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.provider-request-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
