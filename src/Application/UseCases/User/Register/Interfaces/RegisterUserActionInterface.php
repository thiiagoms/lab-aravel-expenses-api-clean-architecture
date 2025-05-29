<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register\Interfaces;

use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Domain\User\Entities\User;

interface RegisterUserActionInterface
{
    public function handle(RegisterUserDTO $dto): User;
}
