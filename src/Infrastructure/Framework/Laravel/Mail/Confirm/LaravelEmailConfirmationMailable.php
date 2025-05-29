<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Mail\Confirm;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;

final class LaravelEmailConfirmationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Name $name,
        private readonly Email $email,
        private readonly string $url
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Confirmation Mailable',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.user.email-confirmation',
            with: [
                'url' => $this->url,
                'name' => $this->name->getValue(),
                'email' => $this->email->getValue(),
            ]
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
