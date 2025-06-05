<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Jobs\Mail\Confirm;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Infrastructure\Framework\Laravel\Mail\Confirm\LaravelEmailConfirmationMailable;

class LaravelSendConfirmationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Name $name,
        private readonly Email $email,
        private readonly string $url
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm your email address',
        );
    }

    public function handle(): void
    {
        Mail::to($this->email->getValue())->send(
            new LaravelEmailConfirmationMailable(
                name: $this->name,
                email: $this->email,
                url: $this->url
            ));
    }
}
