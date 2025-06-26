<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Shared\Validators;

use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;

readonly class VerifyUserEmailIsAvailable
{
    public function __construct(private FindUserByEmailRepositoryInterface $repository) {}

    public function verify(Email $email): void
    {
        $user = $this->repository->find($email);

        if ($user instanceof User) {
            throw EmailAlreadyExistsException::create();
        }
    }
}
