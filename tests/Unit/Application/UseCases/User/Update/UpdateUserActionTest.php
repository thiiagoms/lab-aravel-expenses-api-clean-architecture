<?php

namespace Tests\Unit\Application\UseCases\User\Update;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Find\Interface\FindUserByIdActionInterface;
use Src\Application\UseCases\User\Register\Interfaces\VerifyUserEmailIsAvailableInterface;
use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Application\UseCases\User\Update\Services\UserEntityUpdater;
use Src\Application\UseCases\User\Update\UpdateUserAction;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Update\UpdateUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

final class UpdateUserActionTest extends TestCase
{
    private FindUserByIdActionInterface|MockObject $findUserByIdAction;

    private VerifyUserEmailIsAvailableInterface|MockObject $userEmailIsAvailable;

    private UpdateUserRepositoryInterface|MockObject $repository;

    private TransactionManagerInterface|MockObject $transactionManager;

    private Id $id;

    private UpdateUserAction $action;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->id = new Id('1b7b3983-582d-4fea-b37e-f9a835d430fa');

        $this->findUserByIdAction = $this->createMock(FindUserByIdActionInterface::class);
        $this->userEmailIsAvailable = $this->createMock(VerifyUserEmailIsAvailableInterface::class);
        $this->repository = $this->createMock(UpdateUserRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->action = new UpdateUserAction(
            findUserByIdAction: $this->findUserByIdAction,
            userEmailIsAvailable: $this->userEmailIsAvailable,
            repository: $this->repository,
            transactionManager: $this->transactionManager
        );
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

        $existingUser = $this->getUserMock();

        $this->findUserByIdAction
            ->expects($this->once())
            ->method('handle')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->userEmailIsAvailable
            ->expects($this->atMost(0))
            ->method('verify');

        $user = UserEntityUpdater::update($existingUser, $dto);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturn(new User(
                name: $dto->name(),
                email: $user->email(),
                password: $user->password(),
                id: $user->id(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($existingUser->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($existingUser->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($existingUser->password()->getValue(), $result->password()->getValue());
        $this->assertEquals($existingUser->role(), $result->role());
        $this->assertNotEquals($existingUser->name()->getValue(), $result->name()->getValue());
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

        $existingUser = $this->getUserMock();

        $this->findUserByIdAction
            ->expects($this->once())
            ->method('handle')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->userEmailIsAvailable
            ->expects($this->once())
            ->method('verify')
            ->with($dto->email());

        $user = UserEntityUpdater::update($existingUser, $dto);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturn(new User(
                name: $user->name(),
                email: $dto->email(),
                password: $user->password(),
                id: $user->id(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($existingUser->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($existingUser->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($dto->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($existingUser->password()->getValue(), $result->password()->getValue());
        $this->assertEquals($existingUser->role(), $result->role());
        $this->assertNotEquals($existingUser->email()->getValue(), $result->email()->getValue());
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

        $existingUser = $this->getUserMock();

        $this->findUserByIdAction
            ->expects($this->once())
            ->method('handle')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->userEmailIsAvailable
            ->expects($this->atMost(0))
            ->method('verify');

        $user = UserEntityUpdater::update($existingUser, $dto);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturn(new User(
                name: $user->name(),
                email: $user->email(),
                password: $dto->password(),
                id: $user->id(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($existingUser->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($existingUser->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($existingUser->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($dto->password()->getValue(), $result->password()->getValue());
        $this->assertEquals($existingUser->role(), $result->role());
        $this->assertNotEquals($existingUser->password()->getValue(), $result->password()->getValue());
        $this->assertTrue(
            $result
                ->password()
                ->verifyPasswordMatch('P4ZZY!@#!@WE$#@$!)___.SADASQ#E#DSAD#$@(#$SSw0rd!@#dASD_')
        );
        $this->assertFalse($result->password()->verifyPasswordMatch('P4SSw0rd!@#dASD_'));
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

        $existingUser = $this->getUserMock();

        $this->findUserByIdAction
            ->expects($this->once())
            ->method('handle')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->userEmailIsAvailable
            ->expects($this->once())
            ->method('verify')
            ->with($dto->email());

        $user = UserEntityUpdater::update($existingUser, $dto);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturn(new User(
                name: $dto->name(),
                email: $dto->email(),
                password: $dto->password(),
                id: $user->id(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $result = $this->action->handle($dto);

        $this->assertEquals($existingUser->id()->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($dto->email()->getValue(), $result->email()->getValue());
        $this->assertEquals($dto->password()->getValue(), $result->password()->getValue());
        $this->assertEquals($existingUser->role(), $result->role());
        $this->assertTrue(
            $result
                ->password()
                ->verifyPasswordMatch('P4ZZY!@#!@WE$#@$!)___.SADASQ#E#DSAD#$@(#$SSw0rd!@#dASD_')
        );
        $this->assertNotEquals($existingUser->name()->getValue(), $result->name()->getValue());
        $this->assertNotEquals($existingUser->email()->getValue(), $result->email()->getValue());
        $this->assertNotEquals($existingUser->password()->getValue(), $result->password()->getValue());
        $this->assertFalse($result->password()->verifyPasswordMatch('P4SSw0rd!@#dASD_'));
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_return_user_not_found_exception_when_user_does_not_exist(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('New Name'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4ZZY!@#!@WE$#@$!)___.SADASQ#E#DSAD#$@(#$SSw0rd!@#dASD_')
        );

        $this->findUserByIdAction
            ->expects($this->once())
            ->method('handle')
            ->with($this->id)
            ->willThrowException(UserNotFoundException::create('User not found '));

        $this->userEmailIsAvailable
            ->expects($this->atMost(0))
            ->method('verify');

        $this->repository
            ->expects($this->atMost(0))
            ->method('update');

        $this->transactionManager
            ->expects($this->atMost(0))
            ->method('makeTransaction');

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

        $existingUser = $this->getUserMock();

        $this->findUserByIdAction
            ->expects($this->once())
            ->method('handle')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->userEmailIsAvailable
            ->expects($this->once())
            ->method('verify')
            ->with($dto->email())
            ->willThrowException(EmailAlreadyExistsException::create());

        $this->repository
            ->expects($this->atMost(0))
            ->method('update');

        $this->transactionManager
            ->expects($this->atMost(0))
            ->method('makeTransaction');

        $this->expectException(EmailAlreadyExistsException::class);
        $this->expectExceptionMessage('User with provided e-mail already exists');

        $this->action->handle($dto);
    }

    private function getUserMock(): User
    {
        return new User(
            name: new Name(fake()->name()),
            email: new Email(fake()->email()),
            password: new Password('P4SSw0rd!@#dASD_'),
            id: $this->id,
            createdAt: now()->toDateTimeImmutable(),
            updatedAt: now()->toDateTimeImmutable()
        );
    }
}
