<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Factory;

use DateTimeImmutable;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\Expense\Status\Pending;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

abstract class ExpenseFactory
{
    public static function create(
        User $user,
        Amount $amount,
        Description $description,
        ?StatusInterface $status = null,
        ?Id $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ): Expense {
        return new Expense(
            user: $user,
            amount: $amount,
            description: $description,
            status: $status ?? new Pending,
            id: $id,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
