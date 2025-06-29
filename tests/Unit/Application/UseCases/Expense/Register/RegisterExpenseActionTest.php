<?php

namespace Tests\Unit\Application\UseCases\Expense\Register;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\Expense\Register\DTO\RegisterExpenseDTO;
use Src\Application\UseCases\Expense\Register\Exceptions\UserCannotRegisterExpenseException;
use Src\Application\UseCases\Expense\Register\RegisterExpenseAction;
use Src\Application\UseCases\Expense\Register\Services\RegisterExpenseService;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Pending;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class RegisterExpenseActionTest extends TestCase
{
    private User $user;

    private RegisterExpenseDTO $dto;

    private RegisterExpenseService|MockObject $service;

    private TransactionManagerInterface|MockObject $transactionManager;

    private RegisterExpenseAction $action;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSW0rD!@#_'),
            id: new Id(fake()->uuid()),
            status: new Active,
            emailConfirmedAt: now()->toDateTimeImmutable(),
        );

        $this->dto = new RegisterExpenseDTO(
            userId: $this->user->id(),
            amount: new Amount(100),
            description: new Description('Test Expense Description')
        );

        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->service = $this->createMock(RegisterExpenseService::class);

        $this->action = new RegisterExpenseAction(
            service: $this->service,
            transactionManager: $this->transactionManager
        );
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_create_expense_and_return_created_expense(): void
    {
        $this->service
            ->expects($this->once())
            ->method('register')
            ->with($this->dto)
            ->willReturn(new Expense(
                user: $this->user,
                amount: $this->dto->amount(),
                description: $this->dto->description(),
                id: new Id(fake()->uuid())
            ));

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (\Closure $callback): Expense => $callback());

        $result = $this->action->handle($this->dto);

        $this->assertEquals($this->dto->amount(), $result->amount());
        $this->assertEquals($this->dto->description(), $result->description());
        $this->assertEquals(new Pending, $result->status());
        $this->assertNotNull($result->id());
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_throw_exception_when_user_does_not_exists(): void
    {
        $this->service
            ->expects($this->once())
            ->method('register')
            ->with($this->dto)
            ->willThrowException(UserNotFoundException::create('User not found'));

        $this->transactionManager->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (\Closure $callback): Expense => $callback());

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->action->handle($this->dto);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_should_throw_exception_when_user_cannot_register_expense(): void
    {
        $this->service
            ->expects($this->once())
            ->method('register')
            ->with($this->dto)
            ->willThrowException(UserCannotRegisterExpenseException::create());

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (\Closure $callback): Expense => $callback());

        $this->expectException(UserCannotRegisterExpenseException::class);
        $this->expectExceptionMessage('User cannot register expense');

        $this->action->handle($this->dto);
    }

    #[Test]
    public function it_should_throw_exception_when_transaction_fails(): void
    {
        $this->service
            ->expects($this->never())
            ->method('register');

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willThrowException(new \Exception('Transaction failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction failed');

        $this->action->handle($this->dto);
    }
}
