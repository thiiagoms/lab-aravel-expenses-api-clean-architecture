<?php

namespace Tests\Unit\Application\UseCases\Auth\Authenticate;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\Auth\Authenticate\AuthenticateAction;
use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Application\UseCases\Auth\Authenticate\Interfaces\CheckUserCredentialsInterface;
use Src\Application\UseCases\Auth\Exceptions\InvalidCredentialsException;
use Src\Application\UseCases\Auth\Token\Interfaces\GenerateTokenInterface;
use Src\Domain\Auth\ValueObjects\Token;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;

final class AuthenticateActionTest extends TestCase
{
    private AuthenticateDTO $dto;

    private CheckUserCredentialsInterface|MockObject $credentials;

    private GenerateTokenInterface|MockObject $token;

    private AuthenticateAction $action;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->dto = new AuthenticateDTO(
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password(password: 'P4sSw0RD!@#ASAD_', hashed: false)
        );

        $this->credentials = $this->createMock(CheckUserCredentialsInterface::class);
        $this->token = $this->createMock(GenerateTokenInterface::class);

        $this->action = new AuthenticateAction(
            credentials: $this->credentials,
            token: $this->token
        );

    }

    #[Test]
    public function it_should_throw_invalid_credentials_exception_when_credentials_are_invalid(): void
    {
        $this
            ->credentials->expects($this->once())
            ->method('validate')
            ->with($this->dto)
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Authentication failed. Please check your credentials.');

        $this->action->handle($this->dto);
    }

    #[Test]
    public function it_should_return_user_token_when_credentials_are_valid(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: $this->dto->email(),
            password: new Password(password: $this->dto->password()->getValue()),
            status: new Active
        );

        $token = new Token(
            token: 'user-token',
            type: 'Bearer',
            expiresIn: 3600
        );

        $this->credentials
            ->expects($this->once())
            ->method('validate')
            ->with($this->dto)
            ->willReturn(true);

        $this->credentials
            ->expects($this->once())
            ->method('getAuthenticatedUser')
            ->withAnyParameters()
            ->willReturn($user);

        $this->token
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn($token);

        $token = $this->action->handle($this->dto);

        $this->assertEquals('user-token', $token->token());
        $this->assertEquals('Bearer', $token->type());
        $this->assertEquals(3600, $token->expiresIn());
    }
}
