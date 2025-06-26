<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\User\Register;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Application\UseCases\User\Register\RegisterUserAction;
use Src\Application\UseCases\User\Register\Services\RegisterUserService;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Factory\UserFactory;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class RegisterUserActionTest extends TestCase
{
    private RegisterUserService|MockObject $service;

    private RegisterUserDTO $dto;

    private RegisterUserAction $action;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->dto = new RegisterUserDTO(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSw0ord!@#dASD_')
        );

        $this->service = $this->createMock(RegisterUserService::class);

        $this->action = new RegisterUserAction($this->service);
    }

    /**
     * @throws Exception|\Exception
     */
    #[Test]
    public function it_should_register_user_successfully(): void
    {
        $user = UserFactory::fromDTO($this->dto);

        $this->service
            ->expects($this->once())
            ->method('register')
            ->willReturn(new User(
                name: $user->name(),
                email: $user->email(),
                password: $user->password(),
                id: new Id('123e4567-e89b-12d3-a456-426614174000'),
                role: Role::USER,
                createdAt: $user->createdAt(),
                updatedAt: $user->updatedAt()
            ));

        $result = $this->action->handle($this->dto);

        $this->assertEquals($this->dto->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($this->dto->email()->getValue(), $result->email()->getValue());
        $this->assertEquals(Role::USER, $result->role());
        $this->assertEquals(Status::AWAITING_ACTIVATION, $result->status()->getStatus());
        $this->assertTrue($result->password()->verifyPasswordMatch('P4SSw0ord!@#dASD_'));
    }

    /**
     * @throws EmailAlreadyExistsException|Exception|\Exception
     */
    #[Test]
    public function it_should_throw_exception_when_email_already_exists(): void
    {
        $this->service
            ->expects($this->once())
            ->method('register')
            ->willThrowException(EmailAlreadyExistsException::create());

        $this->expectException(EmailAlreadyExistsException::class);
        $this->expectExceptionMessage('User with provided e-mail already exists');

        $this->action->handle($this->dto);
    }
}
