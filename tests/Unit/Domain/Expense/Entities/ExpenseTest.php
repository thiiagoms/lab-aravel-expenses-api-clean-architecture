<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Expense\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Domain\Expense\Status\Exceptions\InvalidStatusTransitionException;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\Expense\Status\Pending;
use Src\Domain\Expense\Status\Rejected;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\ValueObjects\Id;

class ExpenseTest extends TestCase
{
    private readonly User $user;

    private Amount $amount;

    private Description $description;

    private StatusInterface $status;

    private Id $id;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->user = $this->createMock(User::class);

        $this->amount = new Amount(100.00);

        $this->description = new Description('Test Expense');

        $this->status = new Pending;

        $this->id = new Id(fake()->uuid());
    }

    #[Test]
    public function it_should_create_expense_with_default_status(): void
    {
        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
        );

        $this->assertEquals($this->user, $expense->user());
        $this->assertEquals($this->amount, $expense->amount());
        $this->assertEquals($this->description, $expense->description());
        $this->assertEquals(new Pending, $this->status);
        $this->assertEquals(Status::PENDING, $this->status->getStatus());
        $this->assertEquals(Status::PENDING->value, $this->status->getStatus()->value);
    }

    #[Test]
    public function it_should_allow_change_expense_description(): void
    {
        $newDescription = new Description('Updated Expense Description');

        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: $this->status,
            id: $this->id
        );

        $expense->changeDescription($newDescription);

        $this->assertEquals($newDescription, $expense->description());
        $this->assertNotEquals($this->description, $expense->description());
    }

    #[Test]
    public function it_should_allow_change_expense_amount(): void
    {
        $newAmount = new Amount(200.00);

        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: $this->status,
            id: $this->id
        );

        $expense->changeAmount($newAmount);

        $this->assertEquals($newAmount, $expense->amount());
        $this->assertNotEquals($this->amount, $expense->amount());
    }

    #[Test]
    public function it_should_allow_change_expense_status_from_pending_to_reject(): void
    {
        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: $this->status,
            id: $this->id
        );

        $expense->reject();

        $this->assertEquals(new Rejected, $expense->status());
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_non_admin_role_tries_to_approve_expense(): void
    {
        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: $this->status,
            id: $this->id
        );

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on expense: '%s'.",
                Status::PENDING->value,
                Status::APPROVED->value,
                $expense->id()->getValue()
            )
        );

        $this->user
            ->expects($this->once())
            ->method('role')
            ->willReturn(Role::USER);

        $expense->approve($this->user);
    }

    #[Test]
    public function it_should_allow_only_user_with_admin_role_to_approve_expense(): void
    {
        $this->user
            ->expects($this->once())
            ->method('role')
            ->willReturn(Role::ADMIN);

        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: $this->status,
            id: $this->id
        );

        $expense->approve($this->user);

        $this->assertEquals(Status::APPROVED, $expense->status()->getStatus());
    }

    #[Test]
    public function it_should_throw_exception_when_trying_to_move_approved_expense_to_pending_again(): void
    {
        $this->user
            ->expects($this->once())
            ->method('role')
            ->willReturn(Role::ADMIN);

        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: $this->status,
            id: $this->id
        );

        $expense->approve($this->user);

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on expense: '%s'.",
                Status::APPROVED->value,
                Status::PENDING->value,
                $expense->id()->getValue()
            )
        );

        $expense->pending();
    }

    #[Test]
    public function it_should_throw_exception_when_trying_to_move_approved_expense_to_reject(): void
    {
        $this->user
            ->expects($this->once())
            ->method('role')
            ->willReturn(Role::ADMIN);

        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: $this->status,
            id: $this->id
        );

        $expense->approve($this->user);

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on expense: '%s'.",
                Status::APPROVED->value,
                Status::REJECTED->value,
                $expense->id()->getValue()
            )
        );

        $expense->reject();
    }

    #[Test]
    public function it_should_throw_exception_when_trying_to_move_rejected_expense_to_pending(): void
    {
        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: new Rejected,
            id: $this->id
        );

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on expense: '%s'.",
                Status::REJECTED->value,
                Status::PENDING->value,
                $expense->id()->getValue()
            )
        );

        $expense->pending();
    }

    #[Test]
    public function it_should_throw_exception_when_trying_to_move_rejected_expense_to_approve(): void
    {
        // Even a user with an admin role cannot approve a rejected expense
        $this->user
            ->method('role')
            ->willReturn(Role::ADMIN);

        $expense = new Expense(
            user: $this->user,
            amount: $this->amount,
            description: $this->description,
            status: new Rejected,
            id: $this->id
        );

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on expense: '%s'.",
                Status::REJECTED->value,
                Status::APPROVED->value,
                $expense->id()->getValue()
            )
        );

        $expense->approve($this->user);
    }
}
