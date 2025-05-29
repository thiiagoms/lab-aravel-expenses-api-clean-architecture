<?php

namespace Src\Domain\Repositories\User\Register;

use Src\Domain\User\Entities\User;

interface ConfirmUserEmailRepositoryInterface
{
    public function confirm(User $user): bool;
}
