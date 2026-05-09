<?php

namespace App\Mail;

use App\Models\ProviderRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderRequestRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ProviderRequest $providerRequest,
        public readonly ?string $reason = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your provider application',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.provider-request-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
