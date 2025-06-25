<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Auth\Authenticate;

use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Application\UseCases\Auth\Authenticate\Services\CheckUserCredentialsService;
use Src\Application\UseCases\Auth\Exceptions\InvalidCredentialsException;
use Src\Application\UseCases\Auth\Token\Interfaces\GenerateTokenInterface;
use Src\Domain\Auth\ValueObjects\Token;

final readonly class AuthenticateAction
{
    public function __construct(
        private CheckUserCredentialsService $credentials,
        private GenerateTokenInterface $token
    ) {}

    public function handle(AuthenticateDTO $dto): Token
    {
        $credentialsAreValid = $this->credentials->validate($dto);

        if ($credentialsAreValid === false) {
            throw InvalidCredentialsException::create();
        }

        $user = $this->credentials->getAuthenticatedUser();

        return $this->token->create($user);
    }
}
