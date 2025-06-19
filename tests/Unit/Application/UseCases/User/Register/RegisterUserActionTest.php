<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\User\Register;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\Interfaces\Mail\SendUserConfirmationEmailInterface;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Application\UseCases\User\Register\Interfaces\VerifyUserEmailIsAvailableInterface;
use Src\Application\UseCases\User\Register\RegisterUserAction;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Register\RegisterUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Factory\UserFactory;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;

class RegisterUserActionTest extends TestCase
{
    private VerifyUserEmailIsAvailableInterface|MockObject $verifyUserEmailIsAvailable;

    private SendUserConfirmationEmailInterface|MockObject $confirmationEmail;

    private RegisterUserRepositoryInterface|MockObject $repository;

    private TransactionManagerInterface|MockObject $transactionManager;

    private RegisterUserDTO $dto;

    private RegisterUserAction $action;

    /**
     * @throw Exception
     */
    protected function setUp(): void
    {
        $this->verifyUserEmailIsAvailable = $this->createMock(VerifyUserEmailIsAvailableInterface::class);

        $this->confirmationEmail = $this->createMock(SendUserConfirmationEmailInterface::class);

        $this->repository = $this->createMock(RegisterUserRepositoryInterface::class);

        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->dto = new RegisterUserDTO(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSw0ord!@#dASD_')
        );

        $this->action = new RegisterUserAction(
            verifyEmail: $this->verifyUserEmailIsAvailable,
            sendConfirmationEmail: $this->confirmationEmail,
            repository: $this->repository,
            transaction: $this->transactionManager
        );
    }

    /**
     * @throws EmailAlreadyExistsException|Exception|\Exception
     */
    #[Test]
    public function it_should_throw_exception_when_email_already_exists(): void
    {
        $this
            ->verifyUserEmailIsAvailable
            ->expects($this->once())
            ->method('verify')
            ->with($this->dto->email())
            ->willThrowException(EmailAlreadyExistsException::create());

        $this->expectException(EmailAlreadyExistsException::class);
        $this->expectExceptionMessage('User with provided e-mail already exists');

        $this->action->handle($this->dto);
    }

    /**
     * @throws Exception|\Exception
     */
    #[Test]
    public function it_should_register_user_successfully(): void
    {
        $user = UserFactory::fromDTO($this->dto);

        $this->verifyUserEmailIsAvailable
            ->expects($this->once())
            ->method('verify')
            ->with($this->dto->email());

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $u) use ($user) {
                return $u->email()->getValue() === $user->email()->getValue();
            }))
            ->willReturn($user);

        $this->confirmationEmail
            ->expects($this->once())
            ->method('send')
            ->with($user);

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($this->dto);

        $this->assertEquals($this->dto->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($this->dto->email()->getValue(), $result->email()->getValue());
        $this->assertEquals(Role::USER, $result->role());
        $this->assertEquals(Status::AWAITING_ACTIVATION, $result->status()->getStatus());
        $this->assertTrue($result->password()->verifyPasswordMatch('P4SSw0ord!@#dASD_'));
    }
}
