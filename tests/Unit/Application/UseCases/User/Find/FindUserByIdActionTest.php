<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\User\Find;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Find\FindUserByIdAction;
use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class FindUserByIdActionTest extends TestCase
{
    private Id $id;

    private FindUserByIdRepositoryInterface|MockObject $repository;

    private FindUserByIdAction $action;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->id = new Id(fake()->uuid());

        $this->repository = $this->createMock(FindUserByIdRepositoryInterface::class);

        $this->action = new FindUserByIdAction($this->repository);
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

        $this->action->handle($this->id);
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

        $result = $this->action->handle($this->id);

        $this->assertEquals($this->id, $result->id());
    }
}
