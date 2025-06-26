<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Auth\Authenticate\Services;

use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;

class CheckUserCredentialsService
{
    private ?User $user;

    public function __construct(private readonly FindUserByEmailRepositoryInterface $repository)
    {
        $this->user = null;
    }

    public function validate(AuthenticateDTO $dto): bool
    {
        $user = $this->getUserByEmail($dto->email());

        if ($this->canAuthenticate($user, $dto)) {
            $this->user = $user;

            return true;
        }

        return false;
    }

    public function getAuthenticatedUser(): ?User
    {
        return $this->user;
    }

    private function canAuthenticate(?User $user, AuthenticateDTO $dto): bool
    {
        return ! is_null($user)
            && $this->passwordMatch($user, $dto)
            && $this->userIsActiveAndVerified($user);
    }

    private function getUserByEmail(Email $email): ?User
    {
        return $this->repository->find($email);
    }

    private function passwordMatch(User $user, AuthenticateDTO $dto): bool
    {
        return $user->password()->verifyPasswordMatch($dto->password()->getValue());
    }

    private function userIsActiveAndVerified(User $user): bool
    {
        return $user->status()->getStatus()->isActive() && $user->isEmailAlreadyConfirmed();
    }
}
