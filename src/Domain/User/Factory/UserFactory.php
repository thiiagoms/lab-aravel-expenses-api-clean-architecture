<?php

declare(strict_types=1);

namespace Src\Domain\User\Factory;

use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Domain\User\Entities\User;

abstract class UserFactory
{
    public static function fromDTO(RegisterUserDTO $dto): User
    {
        return new User(
            name: $dto->name(),
            email: $dto->email(),
            password: $dto->password()
        );
    }
}
