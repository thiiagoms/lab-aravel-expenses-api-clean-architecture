<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Services;

use Src\Domain\User\Entities\User;

class CanUserRegisterExpenseService
{
    public function handle(User $user): bool
    {
        return $user->status()->getStatus()->isActive()
            && $user->isEmailAlreadyConfirmed();
    }
}
