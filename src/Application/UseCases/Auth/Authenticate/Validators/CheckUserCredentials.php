<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Auth\Authenticate\Validators;

use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Application\UseCases\Auth\Authenticate\Interfaces\CheckUserCredentialsInterface;
use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;

final class CheckUserCredentials implements CheckUserCredentialsInterface
{
    private ?User $user;

    public function __construct(private readonly FindUserByEmailRepositoryInterface $repository)
    {
        $this->user = null;
    }

    public function validate(AuthenticateDTO $dto): bool
    {
        $user = $this->getUserByEmail($dto->email());

        if ($this->hasValidationFailed($user, $dto)) {
            return false;
        }

        $this->user = $user;

        return true;
    }

    public function getAuthenticatedUser(): ?User
    {
        return $this->user;
    }

    private function hasValidationFailed(?User $user, AuthenticateDTO $dto): bool
    {
        return ! $user || ! $this->isPasswordValid($user, $dto) || ! $this->canUserAuthenticate($user);
    }

    private function getUserByEmail(Email $email): ?User
    {
        return $this->repository->find($email);
    }

    private function isPasswordValid(User $user, AuthenticateDTO $dto): bool
    {
        return $user->password()->verifyPasswordMatch($dto->password()->getValue());
    }

    private function canUserAuthenticate(User $user): bool
    {
        return $user->status()->getStatus()->isActive() && $user->isEmailAlreadyConfirmed();
    }
}
