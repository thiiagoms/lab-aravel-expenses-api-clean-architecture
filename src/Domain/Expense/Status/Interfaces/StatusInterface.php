<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Status\Interfaces;

use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Domain\User\Entities\User;

interface StatusInterface
{
    public function pending(Expense $expense): void;

    public function approve(Expense $expense, User $admin): void;

    public function reject(Expense $expense): void;

    public function getStatus(): Status;
}
