<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Services\Confirm;

use Illuminate\Support\Facades\URL;
use Src\Domain\ValueObjects\Id;

final readonly class LaravelURLGenerator
{
    private const int MAX_URL_MINUTES_TO_EXPIRE = 60;

    public function generate(Id $id): string
    {
        return URL::temporarySignedRoute(
            name: 'api.v1.email-confirmation',
            expiration: now()->addMinutes(self::MAX_URL_MINUTES_TO_EXPIRE),
            parameters: ['id' => $id->getValue()]
        );
    }
}
