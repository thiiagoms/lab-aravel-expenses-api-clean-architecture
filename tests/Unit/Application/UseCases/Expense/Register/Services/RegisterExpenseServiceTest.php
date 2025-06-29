<?php

namespace Tests\Unit\Application\UseCases\Expense\Register\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\Interfaces\Events\EventDispatcherInterface;
use Src\Application\UseCases\Expense\Register\DTO\RegisterExpenseDTO;
use Src\Application\UseCases\Expense\Register\Exceptions\UserCannotRegisterExpenseException;
use Src\Application\UseCases\Expense\Register\Services\RegisterExpenseService;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Events\ExpenseWasRegistered;
use Src\Domain\Expense\Factory\ExpenseFactory;
use Src\Domain\Expense\Services\CanUserRegisterExpenseService;
use Src\Domain\Expense\Status\Pending;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\Repositories\Expense\Register\RegisterExpenseRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class RegisterExpenseServiceTest extends TestCase
{
    private EventDispatcherInterface|MockObject $eventDispatcher;

    private FindOrFailUserByIdService|MockObject $findOrFailUserByIdService;

    private CanUserRegisterExpenseService $permissionChecker;

    private RegisterExpenseRepositoryInterface $repository;

    private RegisterExpenseService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->findOrFailUserByIdService = $this->createMock(FindOrFailUserByIdService::class);

        $this->permissionChecker = new CanUserRegisterExpenseService;

        $this->repository = $this->createMock(RegisterExpenseRepositoryInterface::class);

        $this->service = new RegisterExpenseService(
            eventDispatcher: $this->eventDispatcher,
            userFinder: $this->findOrFailUserByIdService,
            permissionChecker: $this->permissionChecker,
            expenseRepository: $this->repository
        );
    }

    #[Test]
    public function it_should_register_expense(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSW0rD!@#_'),
            id: new Id(fake()->uuid()),
            status: new Active,
            emailConfirmedAt: now()->toDateTimeImmutable(),
        );

        $dto = new RegisterExpenseDTO(
            userId: $user->id(),
            amount: new Amount(100),
            description: new Description('Test Expense Description')
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($dto->userId())
            ->willReturn($user);

        $expense = ExpenseFactory::create(
            user: $user,
            amount: $dto->amount(),
            description: $dto->description()
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Expense $expense) use ($dto, $user) {
                return $expense->user() === $user
                    && $expense->amount()->equals($dto->amount())
                    && $expense->description()->getValue() == $dto->description()->getValue();
            }))
            ->willReturn(new Expense(
                user: $user,
                amount: $dto->amount(),
                description: $dto->description(),
                status: $expense->status(),
                id: new Id(fake()->uuid()),
                createdAt: $expense->createdAt(),
                updatedAt: $expense->updatedAt()
            ));

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (ExpenseWasRegistered $event) use ($dto, $user) {
                return $event->expense->user() === $user
                    && $event->expense->amount()->equals($dto->amount())
                    && $event->expense->description()->getValue() == $dto->description()->getValue();
            }));

        $result = $this->service->register($dto);

        $this->assertEquals($dto->userId()->getValue(), $result->user()->id()->getValue());
        $this->assertEquals($dto->amount()->getValue(), $result->amount()->getValue());
        $this->assertEquals($dto->description()->getValue(), $result->description()->getValue());
        $this->assertEquals(new Pending, $result->status());
        $this->assertNotNull($result->id());
    }

    #[Test]
    public function it_should_throw_exception_when_user_does_not_exists(): void
    {
        $dto = new RegisterExpenseDTO(
            userId: new Id(fake()->uuid()),
            amount: new Amount(100),
            description: new Description('Test Expense Description')
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($dto->userId())
            ->willThrowException(UserNotFoundException::create('User not found'));

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->service->register($dto);
    }

    /**
     * @return StatusInterface[]
     */
    public static function userStatusProvider(): array
    {
        return [
            [new AwaitingActivation],
            [new Suspended],
            [new Banned],
        ];
    }

    #[Test]
    #[DataProvider('userStatusProvider')]
    public function it_should_throw_exception_when_user_cannot_register_expense(StatusInterface $status): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSW0rD!@#_'),
            id: new Id(fake()->uuid()),
            status: $status,
        );

        $dto = new RegisterExpenseDTO(
            userId: $user->id(),
            amount: new Amount(100),
            description: new Description('Test Expense Description')
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($dto->userId())
            ->willReturn($user);

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(UserCannotRegisterExpenseException::class);
        $this->expectExceptionMessage('User cannot register expense');

        $this->service->register($dto);
    }
}
