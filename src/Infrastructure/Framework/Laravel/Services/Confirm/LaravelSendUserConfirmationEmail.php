<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Services\Confirm;

use Src\Application\Interfaces\Mail\SendUserConfirmationEmailInterface;
use Src\Domain\User\Entities\User;
use Src\Infrastructure\Framework\Laravel\Jobs\Mail\Confirm\LaravelSendConfirmationEmailJob;

final readonly class LaravelSendUserConfirmationEmail implements SendUserConfirmationEmailInterface
{
    public function __construct(private LaravelURLGenerator $urlGenerator) {}

    public function send(User $user): void
    {
        $url = $this->urlGenerator->generate($user->id());

        LaravelSendConfirmationEmailJob::dispatch(
            name: $user->name(),
            email: $user->email(),
            url: $url
        );
    }
}
