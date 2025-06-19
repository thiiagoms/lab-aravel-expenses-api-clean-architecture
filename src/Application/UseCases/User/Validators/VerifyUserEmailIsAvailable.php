<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Validators;

use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Register\Interfaces\VerifyUserEmailIsAvailableInterface;
use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;

final readonly class VerifyUserEmailIsAvailable implements VerifyUserEmailIsAvailableInterface
{
    public function __construct(private FindUserByEmailRepositoryInterface $repository) {}

    public function verify(Email $email): void
    {
        $user = $this->repository->find($email);

        if ($user instanceof User) {
            throw EmailAlreadyExistsException::create($email);
        }
    }
}
