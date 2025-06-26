<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register\Services;

use Exception;
use Src\Application\Interfaces\Mail\SendUserConfirmationEmailInterface;
use Src\Application\UseCases\User\Shared\Validators\VerifyUserEmailIsAvailable;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Register\RegisterUserRepositoryInterface;
use Src\Domain\User\Entities\User;

readonly class RegisterUserService
{
    public function __construct(
        private VerifyUserEmailIsAvailable $guardAgainstEmailAlreadyInUse,
        private SendUserConfirmationEmailInterface $sendConfirmationEmail,
        private RegisterUserRepositoryInterface $repository,
        private TransactionManagerInterface $transaction,
    ) {}

    /**
     * @throws Exception
     */
    public function register(User $user): User
    {
        $this->guardAgainstEmailAlreadyInUse->verify($user->email());

        return $this->transaction->makeTransaction(fn (): User => $this->saveAndSendConfirmationEmail($user));
    }

    private function saveAndSendConfirmationEmail(User $user): User
    {
        $user = $this->repository->save($user);

        $this->sendConfirmationEmail->send($user);

        return $user;
    }
}
