<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register\Services;

use Src\Domain\User\Entities\User;

class ConfirmUserEmailService
{
    public function handle(User $user): void
    {
        $user->markEmailAsConfirmed();
        $user->activate();
    }
}
