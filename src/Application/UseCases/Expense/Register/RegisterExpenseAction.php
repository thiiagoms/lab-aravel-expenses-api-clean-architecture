<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Register;

use Exception;
use Src\Application\UseCases\Expense\Register\DTO\RegisterExpenseDTO;
use Src\Application\UseCases\Expense\Register\Exceptions\UserCannotRegisterExpenseException;
use Src\Application\UseCases\Expense\Register\Services\RegisterExpenseService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;

final readonly class RegisterExpenseAction
{
    public function __construct(
        private RegisterExpenseService $service,
        private TransactionManagerInterface $transactionManager,
    ) {}

    /**
     * @throws UserCannotRegisterExpenseException|Exception
     */
    public function handle(RegisterExpenseDTO $dto): Expense
    {
        return $this->transactionManager->makeTransaction(fn (): Expense => $this->service->register($dto));
    }
}
