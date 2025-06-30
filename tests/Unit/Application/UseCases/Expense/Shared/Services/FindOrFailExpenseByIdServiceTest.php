<?php

namespace Tests\Unit\Application\UseCases\Expense\Shared\Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\Expense\Exceptions\ExpenseNotFoundException;
use Src\Application\UseCases\Expense\Shared\Services\FindOrFailExpenseByIdService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Approve;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\Repositories\Expense\Find\FindExpenseByIdRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class FindOrFailExpenseByIdServiceTest extends TestCase
{
    private Id $id;

    private FindExpenseByIdRepositoryInterface|MockObject $repository;

    private FindOrFailExpenseByIdService $service;

    protected function setUp(): void
    {
        $this->id = new Id(fake()->uuid());

        $this->repository = $this->createMock(FindExpenseByIdRepositoryInterface::class);

        $this->service = new FindOrFailExpenseByIdService(repository: $this->repository);
    }

    #[Test]
    public function it_should_return_expense_when_found(): void
    {
        $expense = new Expense(
            id: $this->id,
            user: new User(
                id: new Id(fake()->uuid()),
                name: new Name(fake()->name()),
                email: new Email(fake()->email()),
                password: new Password('P$sSWord123_@#'),
                status: new Active,
                emailConfirmedAt: now()->toDateTimeImmutable()
            ),
            amount: new Amount('132'),
            description: new Description('Test expense description'),
            status: new Approve
        );

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->id)
            ->willReturn($expense);

        $result = $this->service->findOrFail($this->id);

        $this->assertEquals($this->id->getValue(), $result->id()->getValue());
    }

    #[Test]
    public function it_should_throw_exception_when_expense_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->id)
            ->willReturn(null);

        $this->expectException(ExpenseNotFoundException::class);
        $this->expectExceptionMessage('Expense not found');

        $this->service->findOrFail($this->id);
    }
}
