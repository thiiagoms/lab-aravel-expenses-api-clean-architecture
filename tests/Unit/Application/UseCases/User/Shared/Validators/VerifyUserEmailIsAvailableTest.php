<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\User\Shared\Validators;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Shared\Validators\VerifyUserEmailIsAvailable;
use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class VerifyUserEmailIsAvailableTest extends TestCase
{
    private Email $email;

    private VerifyUserEmailIsAvailable $verifyEmail;

    private FindUserByEmailRepositoryInterface|MockObject $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->email = new Email('ilovelaravel@gmail.com');

        $this->repository = $this->createMock(FindUserByEmailRepositoryInterface::class);

        $this->verifyEmail = new VerifyUserEmailIsAvailable($this->repository);
    }

    #[Test]
    public function it_should_throw_exception_when_email_already_exists(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->email)
            ->willReturn(new User(
                name: new Name('John Doe'),
                email: $this->email,
                password: new Password('P4SSw0ord!@#dASD_'),
                id: new Id(fake()->uuid())
            ));

        $this->expectException(EmailAlreadyExistsException::class);
        $this->expectExceptionMessage('User with provided e-mail already exists');

        $this->verifyEmail->verify($this->email);
    }

    #[Test]
    public function it_should_return_null_when_email_is_available(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->email)
            ->willReturn(null);

        $this->verifyEmail->verify($this->email);

        // No return value, just checking if it doesn't throw an exception
        $this->assertTrue(true);
    }
}
