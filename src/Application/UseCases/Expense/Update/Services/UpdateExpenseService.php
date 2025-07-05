<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Update\Services;

use Src\Application\UseCases\Expense\Shared\Services\FindOrFailExpenseByIdService;
use Src\Application\UseCases\Expense\Update\DTO\UpdateExpenseDTO;
use Src\Application\UseCases\Expense\Update\Exceptions\ExpenseCanNotBeUpdatedException;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Repositories\Expense\Update\UpdateExpenseRepositoryInterface;

final readonly class UpdateExpenseService
{
    public function __construct(
        private FindOrFailUserByIdService $findOrFailUserByIdService,
        private ExpenseCanBeUpdatedService $expenseCanBeUpdatedService,
        private FindOrFailExpenseByIdService $findOrFailExpenseByIdService,
        private UpdateExpenseRepositoryInterface $repository,
    ) {}

    /**
     * @throws UserNotFoundException|ExpenseCanNotBeUpdatedException|ExpenseCanNotBeUpdatedException
     */
    public function update(UpdateExpenseDTO $dto): Expense
    {
        $this->findOrFailUserByIdService->findOrFail($dto->userId());

        $expense = $this->findOrFailExpenseByIdService->findOrFail($dto->id());

        $this->expenseCanBeUpdatedService->canBeUpdate($expense, $dto);

        $expense = ExpenseEntityUpdater::update($expense, $dto);

        return $this->repository->update($expense);
    }
}
