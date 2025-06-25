<?php

namespace Tests\Unit\Application\UseCases\User\Shared\Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class FindOrFailUserByIdServiceTest extends TestCase
{
    private Id $id;

    private FindUserByIdRepositoryInterface|MockObject $repository;

    private FindOrFailUserByIdService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->id = new Id(fake()->uuid());

        $this->repository = $this->createMock(FindUserByIdRepositoryInterface::class);

        $this->service = new FindOrFailUserByIdService($this->repository);
    }

    #[Test]
    public function it_should_throw_user_not_found_exception_when_user_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->id)
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->service->findOrFail($this->id);
    }

    #[Test]
    public function it_should_return_user_when_found(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SsW0rd!@#D_'),
            id: $this->id,
        );

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->id)
            ->willReturn($user);

        $result = $this->service->findOrFail($this->id);

        $this->assertEquals($this->id, $result->id());
    }
}
