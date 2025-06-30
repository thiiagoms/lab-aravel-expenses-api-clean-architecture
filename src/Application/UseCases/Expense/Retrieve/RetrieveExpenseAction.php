<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Retrieve;

use Src\Application\UseCases\Expense\Exceptions\ExpenseNotFoundException;
use Src\Application\UseCases\Expense\Shared\Services\FindOrFailExpenseByIdService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\ValueObjects\Id;

final readonly class RetrieveExpenseAction
{
    public function __construct(private FindOrFailExpenseByIdService $service) {}

    /**
     * @throws ExpenseNotFoundException
     */
    public function handle(Id $id): Expense
    {
        return $this->service->findOrFail($id);
    }
}
