<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Status;

use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Domain\Expense\Status\Exceptions\InvalidStatusTransitionException;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\User\Entities\User;

final class Pending implements StatusInterface
{
    public function pending(Expense $expense): void
    {
        throw new InvalidStatusTransitionException(
            from: Status::PENDING,
            to: Status::PENDING,
            expense: $expense
        );
    }

    public function approve(Expense $expense, User $admin): void
    {
        if ($admin->role()->isAdmin()) {
            $expense->changeStatus(new Approve);

            return;
        }

        throw new InvalidStatusTransitionException(
            from: Status::PENDING,
            to: Status::APPROVED,
            expense: $expense
        );
    }

    public function reject(Expense $expense): void
    {
        $expense->changeStatus(new Rejected);
    }

    public function getStatus(): Status
    {
        return Status::PENDING;
    }
}
