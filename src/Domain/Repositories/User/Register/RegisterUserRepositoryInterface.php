<?php

declare(strict_types=1);

namespace Src\Domain\Repositories\User\Register;

use Src\Domain\User\Entities\User;

interface RegisterUserRepositoryInterface
{
    public function save(User $user): User;
}
