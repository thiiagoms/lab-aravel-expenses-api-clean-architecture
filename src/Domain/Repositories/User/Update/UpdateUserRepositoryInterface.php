<?php

namespace Src\Domain\Repositories\User\Update;

use Src\Domain\User\Entities\User;

interface UpdateUserRepositoryInterface
{
    public function update(User $user): User;
}
