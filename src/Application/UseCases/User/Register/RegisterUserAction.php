<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register;

use Exception;
use Src\Application\Interfaces\Mail\SendUserConfirmationEmailInterface;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Application\UseCases\User\Register\Interfaces\RegisterUserActionInterface;
use Src\Application\UseCases\User\Register\Interfaces\VerifyUserEmailIsAvailableInterface;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Register\RegisterUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Factory\UserFactory;

final readonly class RegisterUserAction implements RegisterUserActionInterface
{
    public function __construct(
        private VerifyUserEmailIsAvailableInterface $verifyEmail,
        private SendUserConfirmationEmailInterface $sendConfirmationEmail,
        private RegisterUserRepositoryInterface $repository,
        private TransactionManagerInterface $transaction,
    ) {}

    /**
     * @throws EmailAlreadyExistsException|Exception
     */
    public function handle(RegisterUserDTO $dto): User
    {
        $this->verifyEmail->verify($dto->email());

        $user = UserFactory::fromDTO($dto);

        return $this->transaction->makeTransaction(function () use ($user): User {
            $user = $this->repository->save($user);
            $this->sendConfirmationEmail->send($user);

            return $user;
        });
    }
}
