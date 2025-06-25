<?php

namespace Tests\Unit\Application\UseCases\User\Update\Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Application\UseCases\User\Shared\Validators\VerifyUserEmailIsAvailable;
use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Application\UseCases\User\Update\Services\UpdateUserService;
use Src\Application\UseCases\User\Update\Services\UserEntityUpdater;
use Src\Domain\Repositories\User\Update\UpdateUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class UpdateUserServiceTest extends TestCase
{
    private Id $id;

    private FindOrFailUserByIdService|MockObject $findOrFailUserByIdService;

    private VerifyUserEmailIsAvailable|MockObject $verifyUserEmailIsAvailable;

    private UpdateUserRepositoryInterface|MockObject $repository;

    private UpdateUserService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->id = new Id('1b7b3983-582d-4fea-b37e-f9a835d430fa');

        $this->findOrFailUserByIdService = $this->createMock(FindOrFailUserByIdService::class);

        $this->verifyUserEmailIsAvailable = $this->createMock(VerifyUserEmailIsAvailable::class);

        $this->repository = $this->createMock(UpdateUserRepositoryInterface::class);

        $this->service = new UpdateUserService(
            findOrFailUserByIdService: $this->findOrFailUserByIdService,
            userEmailIsAvailable: $this->verifyUserEmailIsAvailable,
            repository: $this->repository
        );
    }

    #[Test]
    public function it_should_update_only_user_name(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('John PHP developer')
        );

        $existingUser = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSw0ord!@#dASD_'),
            id: $this->id
        );

        $oldName = $existingUser->name();

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->verifyUserEmailIsAvailable
            ->expects($this->never())
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
                status: $user->status(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $result = $this->service->update($dto);

        $this->assertEquals($this->id->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->name()->getValue(), $result->name()->getValue());
        $this->assertNotEquals($oldName->getValue(), $result->name()->getValue());
    }

    #[Test]
    public function it_should_update_only_user_email(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            email: new Email('ilovelphp@gmail.com')
        );

        $existingUser = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSw0ord!@#dASD_'),
            id: $this->id
        );

        $oldEmail = $existingUser->email();

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->verifyUserEmailIsAvailable
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
                status: $user->status(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $result = $this->service->update($dto);

        $this->assertEquals($this->id->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->email()->getValue(), $result->email()->getValue());
        $this->assertNotEquals($oldEmail->getValue(), $result->email()->getValue());
    }

    #[Test]
    public function it_should_update_only_user_password(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            password: new Password('PPHP\ASDTORM_P4SSw0rd!@#dASD_')
        );

        $existingUser = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSw0ord!@#dASD_'),
            id: $this->id
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->verifyUserEmailIsAvailable
            ->expects($this->never())
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
                status: $user->status(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $result = $this->service->update($dto);

        $this->assertEquals($this->id->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->password()->getValue(), $result->password()->getValue());
        $this->assertTrue($result->password()->verifyPasswordMatch('PPHP\ASDTORM_P4SSw0rd!@#dASD_'));
        $this->assertFalse($result->password()->verifyPasswordMatch('P4SSw0rd!@#dASD_'));
    }

    #[Test]
    public function it_should_update_entire_user_data(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('John PHP developer'),
            email: new Email('ilovelphp@gmail.com'),
            password: new Password('PPHP\ASDTORM_P4SSw0rd!@#dASD_')
        );

        $existingUser = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSw0ord!@#dASD_'),
            id: $this->id
        );

        $oldName = $existingUser->name();
        $oldEmail = $existingUser->email();

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->verifyUserEmailIsAvailable
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
                status: $user->status(),
                createdAt: $user->createdAt(),
                updatedAt: now()->toDateTimeImmutable()
            ));

        $result = $this->service->update($dto);

        $this->assertEquals($this->id->getValue(), $result->id()->getValue());
        $this->assertEquals($dto->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($dto->email()->getValue(), $result->email()->getValue());
        $this->assertNotEquals($oldName->getValue(), $result->name()->getValue());
        $this->assertNotEquals($oldEmail->getValue(), $result->email()->getValue());
        $this->assertTrue($result->password()->verifyPasswordMatch('PPHP\ASDTORM_P4SSw0rd!@#dASD_'));
        $this->assertFalse($result->password()->verifyPasswordMatch('P4SSw0rd!@#dASD_'));
    }

    #[Test]
    public function it_should_throw_exception_when_user_id_does_not_exists(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            name: new Name('John PHP developer'),
            email: new Email('ilovelphp@gmail.com'),
            password: new Password('PPHP\ASDTORM_P4SSw0rd!@#dASD_')
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willThrowException(UserNotFoundException::create('User not found'));

        $this->verifyUserEmailIsAvailable
            ->expects($this->never())
            ->method('verify');

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->service->update($dto);
    }

    #[Test]
    public function it_should_throw_exception_when_email_already_exists(): void
    {
        $dto = new UpdateUserDTO(
            id: $this->id,
            email: new Email('ilovelphp@gmail.com')
        );

        $existingUser = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSw0ord!@#dASD_'),
            id: $this->id
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn($existingUser);

        $this->verifyUserEmailIsAvailable
            ->expects($this->once())
            ->method('verify')
            ->with($dto->email())
            ->willThrowException(EmailAlreadyExistsException::create());

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->expectException(EmailAlreadyExistsException::class);
        $this->expectExceptionMessage('User with provided e-mail already exists');

        $this->service->update($dto);
    }
}
