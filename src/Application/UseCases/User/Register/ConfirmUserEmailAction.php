<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register;

use Exception;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Register\Interfaces\ConfirmUserEmailActionInterface;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\Repositories\User\Register\ConfirmUserEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

final readonly class ConfirmUserEmailAction implements ConfirmUserEmailActionInterface
{
    public function __construct(
        private FindUserByIdRepositoryInterface $findUserByIdRepository,
        private ConfirmUserEmailRepositoryInterface $confirmUserEmailRepository,
        private TransactionManagerInterface $transactionManager,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(Id $id): User
    {
        $user = $this->findUserByIdRepository->find($id);

        if ($user === null) {
            throw UserNotFoundException::create("User with id '{$id->getValue()}' not found");
        }

        if ($user->isEmailAlreadyConfirmed()) {
            return $user;
        }

        return $this->transactionManager->makeTransaction(function () use ($user): User {

            $user->markEmailAsConfirmed();
            $user->activate();

            $this->confirmUserEmailRepository->confirm($user);

            return $user;
        });
    }
}
