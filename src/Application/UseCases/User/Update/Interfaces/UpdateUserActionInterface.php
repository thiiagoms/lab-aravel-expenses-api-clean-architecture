<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Update\Interfaces;

use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Domain\User\Entities\User;

interface UpdateUserActionInterface
{
    public function handle(UpdateUserDTO $dto): User;
}
