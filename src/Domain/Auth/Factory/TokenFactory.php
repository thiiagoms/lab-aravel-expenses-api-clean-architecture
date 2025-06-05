<?php

declare(strict_types=1);

namespace Src\Domain\Auth\Factory;

use Src\Domain\Auth\ValueObjects\Token;

abstract class TokenFactory
{
    public static function create(string $token, string $type, int $expiresIn): Token
    {
        return new Token(
            token: $token,
            type: $type,
            expiresIn: $expiresIn
        );
    }
}
