<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Update;

use Exception;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Find\Interface\FindUserByIdActionInterface;
use Src\Application\UseCases\User\Register\Interfaces\VerifyUserEmailIsAvailableInterface;
use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Application\UseCases\User\Update\Interfaces\UpdateUserActionInterface;
use Src\Application\UseCases\User\Update\Services\UserEntityUpdater;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Update\UpdateUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

final readonly class UpdateUserAction implements UpdateUserActionInterface
{
    public function __construct(
        private FindUserByIdActionInterface $findUserByIdAction,
        private VerifyUserEmailIsAvailableInterface $userEmailIsAvailable,
        private UpdateUserRepositoryInterface $repository,
        private TransactionManagerInterface $transactionManager
    ) {}

    /**
     * @throws Exception
     */
    public function handle(UpdateUserDTO $dto): User
    {
        $user = $this->loadExistingUser($dto->id());

        $this->ensureEmailIsAvailable($dto, $user);

        $user = UserEntityUpdater::update($user, $dto);

        return $this->transactionManager->makeTransaction(fn (): User => $this->repository->update($user));
    }

    private function loadExistingUser(Id $id): User
    {
        return $this->findUserByIdAction->handle($id);
    }

    /**
     * @throws EmailAlreadyExistsException
     */
    private function ensureEmailIsAvailable(UpdateUserDTO $dto, User $user): void
    {
        if ($this->shouldSkipEmailValidation($dto, $user)) {
            return;
        }

        $this->userEmailIsAvailable->verify($dto->email());
    }

    private function shouldSkipEmailValidation(UpdateUserDTO $dto, User $user): bool
    {
        return empty($dto->email()) || $dto->email() === $user->email();
    }
}
