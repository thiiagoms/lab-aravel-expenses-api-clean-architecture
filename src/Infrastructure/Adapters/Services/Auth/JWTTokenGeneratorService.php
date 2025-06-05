<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Services\Auth;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Src\Application\UseCases\Auth\Token\Interfaces\GenerateTokenInterface;
use Src\Domain\Auth\Factory\TokenFactory;
use Src\Domain\Auth\ValueObjects\Token;
use Src\Domain\User\Entities\User;
use Src\Infrastructure\Adapters\Mappers\User\UserEntityToUserModelMapper;

final readonly class JWTTokenGeneratorService implements GenerateTokenInterface
{
    private const int SECONDS_PER_MINUTE = 60;

    private const string TOKEN_TYPE = 'Bearer';

    public function __construct(private AuthFactory $guard) {}

    public function create(User $user): Token
    {
        $guard = $this->guard->guard('api');

        $user = UserEntityToUserModelMapper::map($user);

        $token = $guard->fromUser($user);

        if (! $token) {
            throw new \RuntimeException('Failed to generate token');
        }

        $expiresIn = $guard->factory()->getTTL() * self::SECONDS_PER_MINUTE;

        return TokenFactory::create(token: $token, type: self::TOKEN_TYPE, expiresIn: $expiresIn);
    }
}
