<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Update;

use Exception;
use Src\Application\UseCases\Expense\Exceptions\ExpenseNotFoundException;
use Src\Application\UseCases\Expense\Update\DTO\UpdateExpenseDTO;
use Src\Application\UseCases\Expense\Update\Services\UpdateExpenseService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;

final readonly class UpdateExpenseAction
{
    public function __construct(
        private UpdateExpenseService $service,
        private TransactionManagerInterface $transactionManager
    ) {}

    /**
     * @throws ExpenseNotFoundException|Exception
     */
    public function handle(UpdateExpenseDTO $dto): Expense
    {
        return $this->transactionManager->makeTransaction(fn (): Expense => $this->service->update($dto));
    }
}
