<?php

namespace Tests\Unit\Application\UseCases\User\Update;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Application\UseCases\User\Update\Services\UpdateUserService;
use Src\Application\UseCases\User\Update\UpdateUserAction;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

final class UpdateUserActionTest extends TestCase
{
    private UpdateUserService|MockObject $service;

    private TransactionManagerInterface|MockObject $transactionManager;

    private Id $id;

    private UpdateUserAction $action;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->id = new Id('1b7b3983-582d-4fea-b37e-f9a835d430fa');

        $this->service = $this->createMock(UpdateUserService::class);

        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->action = new UpdateUserAction(service: $this->service, transactionManager: $this->transactionManager);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_update_only_user_name_and_return_updated_user_data(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('New Name')
        );

        $userMock = $this->getUserMock(name: $dto->name());

        $this->service
            ->expects($this->once())
            ->method('update')
            ->with($dto)
            ->willReturn($userMock);

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($dto->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($userMock->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($userMock->password()->getValue(), $result->password()->getValue());
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_update_ony_user_email_and_return_updated_user_data(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            email: new Email('ilovelaravel@gmail.com')
        );

        $userMock = $this->getUserMock(email: $dto->email());

        $this->service
            ->expects($this->once())
            ->method('update')
            ->with($dto)
            ->willReturn($userMock);

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($dto->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($userMock->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($userMock->password()->getValue(), $result->password()->getValue());
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_update_only_user_password_and_return_updated_user_data(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            password: new Password('P4ZZY!@#!@WE$#@$!)___.SADASQ#E#DSAD#$@(#$SSw0rd!@#dASD_')
        );

        $userMock = $this->getUserMock(password: $dto->password());

        $this->service
            ->expects($this->once())
            ->method('update')
            ->with($dto)
            ->willReturn($userMock);

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($dto->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->password()->getValue(), $result->password()->getValue());
        $this->assertEquals($userMock->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($userMock->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($userMock->password()->getValue(), $result->password()->getValue());
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_update_user_name_and_email_and_password_and_return_updated_user_data(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('New Name'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4ZZY!@#!@WE$#@$!)___.SADASQ#E#DSAD#$@(#$SSw0rd!@#dASD_')
        );

        $userMock = $this->getUserMock(
            name: $dto->name(),
            email: $dto->email(),
            password: $dto->password()
        );

        $this->service
            ->expects($this->once())
            ->method('update')
            ->with($dto)
            ->willReturn($userMock);

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($dto->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($dto->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($dto->password()->getValue(), $result->password()->getValue());
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_return_user_not_found_exception_when_user_does_not_exist(): void
    {
        $dto = new UpdateUserDTO(id: $this->id);

        $this->service
            ->expects($this->once())
            ->method('update')
            ->with($dto)
            ->willThrowException(UserNotFoundException::create('User not found '));

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->action->handle($dto);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_return_email_already_exists_exception_when_email_is_already_taken(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('New Name'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4ZZY!@#!@WE$#@$!)___.SADASQ#E#DSAD#$@(#$SSw0rd!@#dASD_')
        );

        $this->service
            ->expects($this->once())
            ->method('update')
            ->with($dto)
            ->willThrowException(EmailAlreadyExistsException::create());

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $this->expectException(EmailAlreadyExistsException::class);
        $this->expectExceptionMessage('User with provided e-mail already exists');

        $this->action->handle($dto);
    }

    #[Test]
    public function it_should_throw_exception_when_transaction_fails(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('New Name'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4ZZY!@#!@WE$#@$!)___.SADASQ#E#DSAD#$@(#$SSw0rd!@#dASD_')
        );

        $this->service
            ->expects($this->never())
            ->method('update');

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willThrowException(new \Exception('Transaction failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction failed');

        $this->action->handle($dto);
    }

    private function getUserMock(?Name $name = null, ?Email $email = null, ?Password $password = null): User
    {
        $name = $name ?? new Name(fake()->name());
        $email = $email ?? new Email(fake()->freeEmail());
        $password = $password ?? new Password('P4SSw0rd!@#dASD_');

        return new User(
            name: $name,
            email: $email,
            password: $password,
            id: $this->id,
            createdAt: now()->toDateTimeImmutable(),
            updatedAt: now()->toDateTimeImmutable()
        );
    }
}
