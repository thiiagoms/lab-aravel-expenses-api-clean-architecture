<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\User\Register\Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\Interfaces\Mail\SendUserConfirmationEmailInterface;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Register\Services\RegisterUserService;
use Src\Application\UseCases\User\Shared\Validators\VerifyUserEmailIsAvailable;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Register\RegisterUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class RegisterUserServiceTest extends TestCase
{
    private VerifyUserEmailIsAvailable|MockObject $guardAgainstEmailAlreadyInUse;

    private SendUserConfirmationEmailInterface|MockObject $sendUserConfirmationEmail;

    private RegisterUserRepositoryInterface|MockObject $repository;

    private TransactionManagerInterface $transactionManager;

    private User $user;

    private RegisterUserService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->guardAgainstEmailAlreadyInUse = $this->createMock(VerifyUserEmailIsAvailable::class);

        $this->sendUserConfirmationEmail = $this->createMock(SendUserConfirmationEmailInterface::class);

        $this->repository = $this->createMock(RegisterUserRepositoryInterface::class);

        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->user = new User(
            name: new Name('John Doe'),
            email: new Email(fake()->freeEmail()),
            password: new Password('P4SSw0ord!@#dASD_')
        );

        $this->service = new RegisterUserService(
            guardAgainstEmailAlreadyInUse: $this->guardAgainstEmailAlreadyInUse,
            sendConfirmationEmail: $this->sendUserConfirmationEmail,
            repository: $this->repository,
            transaction: $this->transactionManager
        );
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_register_user_and_return_created_user_data(): void
    {
        $this->guardAgainstEmailAlreadyInUse
            ->expects($this->once())
            ->method('verify')
            ->with($this->user->email());

        $createdUser = new User(
            name: $this->user->name(),
            email: $this->user->email(),
            password: $this->user->password(),
            id: new Id('123e4567-e89b-12d3-a456-426614174000'),
            status: $this->user->status(),
            createdAt: $this->user->createdAt(),
            updatedAt: $this->user->updatedAt()
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn($createdUser);

        $this->sendUserConfirmationEmail
            ->expects($this->once())
            ->method('send')
            ->with($createdUser);

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (\Closure $callback): User => $callback());

        $result = $this->service->register($this->user);

        $this->assertEquals($this->user->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($this->user->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($this->user->password()->getValue(), $result->password()->getValue());
        $this->assertNotNull($result->id()->getValue());
        $this->assertEquals(new AwaitingActivation, $result->status());
        $this->assertNotNull($result->createdAt());
        $this->assertNotNull($result->updatedAt());
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_throw_email_already_exists_exception_when_email_is_already_in_use(): void
    {
        $this->guardAgainstEmailAlreadyInUse
            ->expects($this->once())
            ->method('verify')
            ->with($this->user->email())
            ->willThrowException(EmailAlreadyExistsException::create());

        $this->sendUserConfirmationEmail
            ->expects($this->never())
            ->method('send');

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->transactionManager
            ->expects($this->never())
            ->method('makeTransaction');

        $this->expectException(EmailAlreadyExistsException::class);
        $this->expectExceptionMessage('User with provided e-mail already exists');

        $this->service->register($this->user);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_throw_exception_when_transaction_fails(): void
    {
        $this->guardAgainstEmailAlreadyInUse
            ->expects($this->once())
            ->method('verify')
            ->with($this->user->email());

        $createdUser = new User(
            name: $this->user->name(),
            email: $this->user->email(),
            password: $this->user->password(),
            id: new Id('123e4567-e89b-12d3-a456-426614174000'),
            status: $this->user->status(),
            createdAt: $this->user->createdAt(),
            updatedAt: $this->user->updatedAt()
        );

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willThrowException(new \Exception('Transaction failed'));

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->sendUserConfirmationEmail
            ->expects($this->never())
            ->method('send');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction failed');

        $this->service->register($this->user);
    }
}
