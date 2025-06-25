<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Update;

use Exception;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Application\UseCases\User\Update\Services\UpdateUserService;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\User\Entities\User;

final readonly class UpdateUserAction
{
    public function __construct(
        private UpdateUserService $service,
        private TransactionManagerInterface $transactionManager
    ) {}

    /**
     * @throws EmailAlreadyExistsException|Exception
     */
    public function handle(UpdateUserDTO $dto): User
    {
        return $this->transactionManager->makeTransaction(fn (): User => $this->service->update($dto));
    }
}
