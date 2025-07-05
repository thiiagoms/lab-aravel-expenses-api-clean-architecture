<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Register\Services;

use Src\Application\Interfaces\Events\EventDispatcherInterface;
use Src\Application\UseCases\Expense\Register\DTO\RegisterExpenseDTO;
use Src\Application\UseCases\Expense\Register\Exceptions\UserCannotRegisterExpenseException;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Events\ExpenseWasRegistered;
use Src\Domain\Expense\Factory\ExpenseFactory;
use Src\Domain\Expense\Services\CanUserRegisterExpenseService;
use Src\Domain\Repositories\Expense\Register\RegisterExpenseRepositoryInterface;

readonly class RegisterExpenseService
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private FindOrFailUserByIdService $userFinder,
        private CanUserRegisterExpenseService $permissionChecker,
        private RegisterExpenseRepositoryInterface $expenseRepository,
    ) {}

    public function register(RegisterExpenseDTO $dto): Expense
    {
        $user = $this->userFinder->findOrFail($dto->userId());

        if (! $this->permissionChecker->handle($user)) {
            throw UserCannotRegisterExpenseException::create();
        }

        $expense = ExpenseFactory::create(
            user: $user,
            amount: $dto->amount(),
            description: $dto->description()
        );

        $expense = $this->expenseRepository->save($expense);

        $this->eventDispatcher->dispatch(new ExpenseWasRegistered($expense));

        return $expense;
    }
}
