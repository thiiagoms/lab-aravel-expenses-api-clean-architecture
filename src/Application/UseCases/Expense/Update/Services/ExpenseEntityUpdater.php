<?php

namespace Src\Application\UseCases\Expense\Update\Services;

use Src\Application\UseCases\Expense\Update\DTO\UpdateExpenseDTO;
use Src\Domain\Expense\Entities\Expense;

abstract class ExpenseEntityUpdater
{
    public static function update(Expense $expense, UpdateExpenseDTO $dto): Expense
    {
        return new Expense(
            user: $expense->user(),
            amount: $dto->amount() ?? $expense->amount(),
            description: $dto->description() ?? $expense->description(),
            status: $expense->status(),
            id: $expense->id(),
            createdAt: $expense->createdAt(),
            updatedAt: new \DateTimeImmutable()
        );
    }
}
