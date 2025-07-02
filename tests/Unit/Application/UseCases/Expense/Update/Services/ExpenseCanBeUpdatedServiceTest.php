<?php

namespace Tests\Unit\Application\UseCases\Expense\Update\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\Expense\Update\DTO\UpdateExpenseDTO;
use Src\Application\UseCases\Expense\Update\Exceptions\ExpenseCanNotBeUpdatedException;
use Src\Application\UseCases\Expense\Update\Services\ExpenseCanBeUpdatedService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Approve;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\Expense\Status\Rejected;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class ExpenseCanBeUpdatedServiceTest extends TestCase
{
    private ExpenseCanBeUpdatedService $service;

    protected function setUp(): void
    {
        $this->service = new ExpenseCanBeUpdatedService;
    }

    public static function expenseStatusProvider(): array
    {
        return [
            [new Approve],
            [new Rejected],
        ];
    }

    #[Test]
    #[DataProvider('expenseStatusProvider')]
    public function it_should_throw_exception_when_expense_is_not_valid_to_update(StatusInterface $status): void
    {
        $user = new User(
            name: new Name(fake()->name()),
            email: new Email(fake()->email()),
            password: new Password('P3sSw0rd!@#_Ad'),
            id: new Id(fake()->uuid()),
            status: new Active,
        );

        $expense = new Expense(
            user: $user,
            amount: new Amount('12'),
            description: new Description('Expense Description'),
            status: $status,
            id: new Id(fake()->uuid()),
        );

        $dto = new UpdateExpenseDTO(
            id: $expense->id(),
            userId: $user->id(),
        );

        $this->expectException(ExpenseCanNotBeUpdatedException::class);
        $this->expectExceptionMessage('Only pending expenses can be updated.');

        $this->service->canBeUpdate(expense: $expense, dto: $dto);
    }

    #[Test]
    public function it_should_throw_exception_when_user_does_not_own_expense(): void
    {
        $user = new User(
            name: new Name(fake()->name()),
            email: new Email(fake()->email()),
            password: new Password('P3sSw0rd!@#_Ad'),
            id: new Id(fake()->uuid()),
            status: new Active,
        );

        $expense = new Expense(
            user: new User(
                name: new Name(fake()->name()),
                email: new Email(fake()->email()),
                password: new Password('P3sSw0rd!@#_Ad'),
                id: new Id(fake()->uuid()),
                status: new Active,
            ),
            amount: new Amount('12'),
            description: new Description('Expense Description'),
            id: new Id(fake()->uuid()),
        );

        $dto = new UpdateExpenseDTO(
            id: $expense->id(),
            userId: $user->id(),
        );

        $this->expectException(ExpenseCanNotBeUpdatedException::class);
        $this->expectExceptionMessage('The authenticated user does not own this expense.');

        $this->service->canBeUpdate(expense: $expense, dto: $dto);
    }
}
