<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Update\Services;

use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Domain\User\Entities\User;

abstract class UserEntityUpdater
{
    public static function update(User $user, UpdateUserDTO $dto): User
    {
        return new User(
            name: $dto->name() ?? $user->name(),
            email: $dto->email() ?? $user->email(),
            password: $dto->password() ?? $user->password(),
            id: $user->id(),
            role: $user->role(),
            status: $user->status(),
            createdAt: $user->createdAt(),
            updatedAt: $user->updatedAt()
        );
    }
}
