<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Destroy;

use Src\Application\UseCases\Expense\Shared\Services\FindOrFailExpenseByIdService;
use Src\Domain\Repositories\Expense\Destroy\DestroyExpenseRepositoryInterface;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\ValueObjects\Id;

final readonly class DestroyExpenseAction
{
    public function __construct(
        private TransactionManagerInterface $transactionManager,
        private FindOrFailExpenseByIdService $findOrFailExpenseByIdService,
        private DestroyExpenseRepositoryInterface $repository,
    ) {}

    /**
     * @throws \Exception
     */
    public function handle(Id $id): bool
    {
        $this->findOrFailExpenseByIdService->findOrFail($id);

        return $this->transactionManager->makeTransaction(fn (): bool => $this->repository->destroy($id));
    }
}
