<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Shared\Services;

use Src\Application\UseCases\Expense\Exceptions\ExpenseNotFoundException;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Repositories\Expense\Find\FindExpenseByIdRepositoryInterface;
use Src\Domain\ValueObjects\Id;

readonly class FindOrFailExpenseByIdService
{
    public function __construct(private FindExpenseByIdRepositoryInterface $repository) {}

    public function findOrFail(Id $id): Expense
    {
        $expense = $this->repository->find($id);

        if ($expense === null) {
            throw ExpenseNotFoundException::create();
        }

        return $expense;
    }
}
