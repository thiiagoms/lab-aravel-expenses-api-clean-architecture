<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register;

use Exception;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Register\Services\ConfirmUserEmailService;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Register\ConfirmUserEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

readonly class ConfirmUserEmailAction
{
    public function __construct(
        private ConfirmUserEmailService $confirmUserEmailService,
        private FindOrFailUserByIdService $findOrFailUserService,
        private TransactionManagerInterface $transactionManager,
        private ConfirmUserEmailRepositoryInterface $confirmUserEmailRepository
    ) {}

    /**
     * @throws UserNotFoundException|Exception
     */
    public function handle(Id $id): User
    {
        $user = $this->findOrFailUserService->findOrFail($id);

        if ($user->isEmailAlreadyConfirmed()) {
            return $user;
        }

        return $this->transactionManager->makeTransaction(fn (): User => $this->confirmUserEmail($user));
    }

    private function confirmUserEmail(User $user): User
    {
        $this->confirmUserEmailService->handle($user);

        $this->confirmUserEmailRepository->confirm($user);

        return $user;
    }
}
