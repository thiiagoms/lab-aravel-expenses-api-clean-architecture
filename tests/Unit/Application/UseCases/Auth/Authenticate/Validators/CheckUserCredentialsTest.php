<?php

namespace Tests\Unit\Application\UseCases\Auth\Authenticate\Validators;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Application\UseCases\Auth\Authenticate\Validators\CheckUserCredentials;
use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;

class CheckUserCredentialsTest extends TestCase
{
    private AuthenticateDTO $dto;

    private FindUserByEmailRepositoryInterface $repository;

    private CheckUserCredentials $credentials;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->dto = new AuthenticateDTO(
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password(password: 'P4sSw0RD!@#ASAD_', hashed: false)
        );

        $this->repository = $this->createMock(FindUserByEmailRepositoryInterface::class);

        $this->credentials = new CheckUserCredentials($this->repository);
    }

    #[Test]
    public function it_should_return_false_if_user_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->dto->email())
            ->willReturn(null);

        $result = $this->credentials->validate($this->dto);

        $this->assertFalse($result);
        $this->assertNull($this->credentials->getAuthenticatedUser());
    }

    #[Test]
    public function it_should_return_false_if_password_does_not_match(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: $this->dto->email(),
            password: new Password('P4$#$!SAD_!@#Dc'),
            status: new Active
        );

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->dto->email())
            ->willReturn($user);

        $result = $this->credentials->validate($this->dto);

        $this->assertFalse($result);
        $this->assertNull($this->credentials->getAuthenticatedUser());
    }

    #[Test]
    public function it_should_return_true_if_credentials_are_valid(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: $this->dto->email(),
            password: new Password($this->dto->password()->getValue()),
            status: new Active,
            emailConfirmedAt: new \DateTimeImmutable
        );

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->dto->email())
            ->willReturn($user);

        $result = $this->credentials->validate($this->dto);

        $this->assertTrue($result);
        $this->assertEquals($user, $this->credentials->getAuthenticatedUser());
    }
}
