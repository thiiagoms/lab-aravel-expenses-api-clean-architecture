<?php

namespace Tests\Unit\Infrastructure\Adapters\Services\Auth;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Domain\Auth\ValueObjects\Token;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Adapters\Mappers\User\UserEntityToUserModelMapper;
use Src\Infrastructure\Adapters\Services\Auth\JWTTokenGeneratorService;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tymon\JWTAuth\JWTGuard;

class JWTTokenGeneratorServiceTest extends TestCase
{
    private User $user;

    private JWTGuard $guard;

    private MockObject $factory;

    private AuthFactory $authFactory;

    private JWTTokenGeneratorService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->user = new User(
            name: new Name('John Doe'),
            email: new Email('john.doe@example.com'),
            password: new Password('p4sSw0rr123!@#_'),
            id: new Id(fake()->uuid()),
        );

        $this->guard = $this->createMock(JWTGuard::class);
        $this->factory = $this->createMock(\stdClass::class);

        $this->authFactory = $this->createMock(AuthFactory::class);

        $this->authFactory
            ->expects($this->any())
            ->method('guard')
            ->with('api')
            ->willReturn($this->guard);

        $this->service = new JwtTokenGeneratorService($this->authFactory);
    }

    #[Test]
    public function itShouldGenerateTokenSuccessfully(): void
    {
        $expectedToken = 'fake.jwt.token';
        $expectedTtl = 60;
        $expectedExpiresIn = $expectedTtl * 60;

        $userLaravelModel = UserEntityToUserModelMapper::map($this->user);

        $this->guard
            ->expects($this->once())
            ->method('fromUser')
            ->with($this->callback(fn (LaravelUserModel $model): bool => $model === $userLaravelModel))
            ->willReturn($expectedToken);

        $this->guard
            ->expects($this->once())
            ->method('factory')
            ->willReturn($this->factory);

        $this->factory
            ->expects($this->once())
            ->method('getTTL')
            ->willReturn($expectedTtl);

        $token = $this->service->create($this->user);

        $this->assertSame($expectedToken, $token->token());
        $this->assertSame('Bearer', $token->type());
        $this->assertSame($expectedExpiresIn, $token->expiresIn());
    }
}
