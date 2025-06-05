<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\User\Register;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Register\ConfirmUserEmailAction;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\Repositories\User\Register\ConfirmUserEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

final class ConfirmUserEmailActionTest extends TestCase
{
    private Id $id;

    private FindUserByIdRepositoryInterface|MockObject $findUserByIdRepository;

    private ConfirmUserEmailRepositoryInterface|MockObject $confirmUserEmailRepository;

    private TransactionManagerInterface|MockObject $transactionManager;

    private ConfirmUserEmailAction $action;

    protected function setUp(): void
    {
        $this->id = new Id(fake()->uuid());

        $this->findUserByIdRepository = $this->createMock(FindUserByIdRepositoryInterface::class);
        $this->confirmUserEmailRepository = $this->createMock(ConfirmUserEmailRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->action = new ConfirmUserEmailAction(
            findUserByIdRepository: $this->findUserByIdRepository,
            confirmUserEmailRepository: $this->confirmUserEmailRepository,
            transactionManager: $this->transactionManager
        );
    }

    #[Test]
    public function it_should_throws_exception_when_user_not_found(): void
    {
        $this->findUserByIdRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->id)
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User with id '{$this->id->getValue()}' not found");

        $this->action->handle($this->id);
    }

    #[Test]
    public function it_should_returns_user_if_email_already_confirmed(): void
    {
        $user = $this->createMock(User::class);

        $this->findUserByIdRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->id)
            ->willReturn($user);

        $user->method('isEmailAlreadyConfirmed')->willReturn(true);

        $this->transactionManager
            ->expects($this->never())
            ->method('makeTransaction');

        $result = $this->action->handle($this->id);

        $this->assertEquals($user, $result);
    }

    #[Test]
    public function it_should_confirms_user_email_and_activates_user(): void
    {
        $user = $this->createMock(User::class);

        $this->findUserByIdRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->id)
            ->willReturn($user);

        $user->method('isEmailAlreadyConfirmed')->willReturn(false);

        $user->expects($this->any())->method('markEmailAsConfirmed');
        $user->expects($this->any())->method('activate');

        $this->confirmUserEmailRepository
            ->expects($this->any())
            ->method('confirm')
            ->with($user)
            ->willReturn(true);

        $this->transactionManager
            ->expects($this->any())
            ->method('makeTransaction')
            ->with($this->callback(function ($callback) use ($user): bool {
                $result = $callback();

                return $result === $user;
            }))
            ->willReturnCallback(function ($callback) {
                return $callback();
            });

        $result = $this->action->handle($this->id);

        $this->assertEquals($user, $result);
    }
}
