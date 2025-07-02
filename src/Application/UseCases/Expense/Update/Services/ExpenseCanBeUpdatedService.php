<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Update\Services;

use Src\Application\UseCases\Expense\Update\DTO\UpdateExpenseDTO;
use Src\Application\UseCases\Expense\Update\Exceptions\ExpenseCanNotBeUpdatedException;
use Src\Domain\Expense\Entities\Expense;

final readonly class ExpenseCanBeUpdatedService
{
    public function canBeUpdate(Expense $expense, UpdateExpenseDTO $dto): void
    {
        $this->ensureExpenseIsPending($expense);
        $this->ensureUserOwnsExpense($expense, $dto);
    }

    /**
     * @throws ExpenseCanNotBeUpdatedException
     */
    private function ensureExpenseIsPending(Expense $expense): void
    {
        if (! $expense->status()->getStatus()->isPending()) {
            throw ExpenseCanNotBeUpdatedException::create(
                'Only pending expenses can be updated.'
            );
        }
    }

    private function ensureUserOwnsExpense(Expense $expense, UpdateExpenseDTO $dto): void
    {
        if ($expense->user()->id()->getValue() !== $dto->userId()->getValue()) {
            throw ExpenseCanNotBeUpdatedException::create(
                'The authenticated user does not own this expense.'
            );
        }
    }
}
