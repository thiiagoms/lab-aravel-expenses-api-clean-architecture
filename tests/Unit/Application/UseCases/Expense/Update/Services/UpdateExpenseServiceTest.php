<?php

namespace Tests\Unit\Application\UseCases\Expense\Update\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\Expense\Exceptions\ExpenseNotFoundException;
use Src\Application\UseCases\Expense\Shared\Services\FindOrFailExpenseByIdService;
use Src\Application\UseCases\Expense\Update\DTO\UpdateExpenseDTO;
use Src\Application\UseCases\Expense\Update\Exceptions\ExpenseCanNotBeUpdatedException;
use Src\Application\UseCases\Expense\Update\Services\ExpenseCanBeUpdatedService;
use Src\Application\UseCases\Expense\Update\Services\UpdateExpenseService;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Approve;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\Expense\Status\Rejected;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\Repositories\Expense\Update\UpdateExpenseRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class UpdateExpenseServiceTest extends TestCase
{
    private FindOrFailUserByIdService|MockObject $findOrFailUserByIdService;

    private ExpenseCanBeUpdatedService $expenseCanBeUpdatedService;

    private FindOrFailExpenseByIdService|MockObject $findOrFailExpenseByIdService;

    private UpdateExpenseRepositoryInterface|MockObject $repository;

    private UpdateExpenseService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->findOrFailUserByIdService = $this->createMock(FindOrFailUserByIdService::class);

       $this->expenseCanBeUpdatedService = new ExpenseCanBeUpdatedService;

       $this->findOrFailExpenseByIdService = $this->createMock(FindOrFailExpenseByIdService::class);

        $this->repository = $this->createMock(UpdateExpenseRepositoryInterface::class);

        $this->service = new UpdateExpenseService(
            findOrFailUserByIdService: $this->findOrFailUserByIdService,
            expenseCanBeUpdatedService: $this->expenseCanBeUpdatedService,
            findOrFailExpenseByIdService: $this->findOrFailExpenseByIdService,
            repository: $this->repository,
        );
    }

    #[Test]
    public function itShouldThrowExceptionWhenUserDoesNotExists(): void
    {
        $expenseDTO = new UpdateExpenseDTO(
            id:  new Id('a757d8e8-af2f-439d-b0de-c8dcd77d54e5'),
            userId: new Id('12345678-1234-1234-1234-123456789012'),
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->userId())
            ->willThrowException(UserNotFoundException::create('User not found'));

        $this->findOrFailExpenseByIdService
            ->expects($this->never())
            ->method('findOrFail');

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->service->update($expenseDTO);
    }

    #[Test]
    public function itShouldThrowExceptionWhenExpenseDoesNotExists(): void
    {
        $expenseDTO = new UpdateExpenseDTO(
            id:  new Id('a757d8e8-af2f-439d-b0de-c8dcd77d54e5'),
            userId: new Id('12345678-1234-1234-1234-123456789012'),
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->userId());

        $this->findOrFailExpenseByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->id())
            ->willThrowException(ExpenseNotFoundException::create());

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->expectException(ExpenseNotFoundException::class);
        $this->expectExceptionMessage('Expense not found');

        $this->service->update($expenseDTO);
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
    public function itShouldThrowExceptionWhenExpenseCannotBeUpdated(StatusInterface $status): void
    {
        $expenseDTO = new UpdateExpenseDTO(
            id:  new Id('a757d8e8-af2f-439d-b0de-c8dcd77d54e5'),
            userId: new Id('12345678-1234-1234-1234-123456789012'),
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->userId());

        $this->findOrFailExpenseByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->id())
            ->willReturn(new Expense(
               user: new User(
                    name: new Name('John Doe'),
                    email: new Email('ilovelaravel@gmail.com'),
                    password: new Password('P4sSW0rd!@#)'),
                    id: new Id(fake()->uuid())
                ),
                amount: new Amount('100'),
                description: new Description('Test Expense'),
                status: $status,
                id: new Id(fake()->uuid())
            ));

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->expectException(ExpenseCanNotBeUpdatedException::class);
        $this->expectExceptionMessage('Only pending expenses can be updated.');

        $this->service->update($expenseDTO);
    }

    #[Test]
    public function itShouldThrowExceptionWhenUserExpenseDoesNotOwnExpense(): void
    {
        $expenseDTO = new UpdateExpenseDTO(
            id:  new Id('a757d8e8-af2f-439d-b0de-c8dcd77d54e5'),
            userId: new Id('12345678-1234-1234-1234-123456789012'),
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->userId());

        $this->findOrFailExpenseByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->id())
            ->willReturn(new Expense(
                user: new User(
                    name: new Name('John Doe'),
                    email: new Email('ilovelaravel@gmail.com'),
                    password: new Password('P4sSW0rd!@#)'),
                    id: new Id(fake()->uuid())
                ),
                amount: new Amount('100'),
                description: new Description('Test Expense'),
                id: $expenseDTO->id()
            ));

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->expectException(ExpenseCanNotBeUpdatedException::class);
        $this->expectExceptionMessage('The authenticated user does not own this expense.');

        $this->service->update($expenseDTO);
    }

    #[Test]
    public function itShouldUpdateOnlyExpenseAmount(): void
    {
        $expenseDTO = new UpdateExpenseDTO(
            id:  new Id('a757d8e8-af2f-439d-b0de-c8dcd77d54e5'),
            userId: new Id('12345678-1234-1234-1234-123456789012'),
            amount: new Amount('1000')
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->userId());

        $existingExpense = new Expense(
            user: new User(
                name: new Name('John Doe'),
                email: new Email('ilovelaravel@gmail.com'),
                password: new Password('P4sSW0rd!@#)'),
                id: $expenseDTO->userId()
            ),
            amount: new Amount('12'),
            description: new Description('Test Expense'),
            id: $expenseDTO->id()
        );

        $this->findOrFailExpenseByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->id())
            ->willReturn($existingExpense);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturn(new Expense(
                user: new User(
                    name: new Name('John Doe'),
                    email: new Email('ilovelaravel@gmail.com'),
                    password: new Password('P4sSW0rd!@#)'),
                    id: $expenseDTO->userId()
                ),
                amount: $expenseDTO->amount(),
                description: $existingExpense->description(),
                status: $existingExpense->status(),
                id: $existingExpense->id(),
                createdAt: $existingExpense->createdAt(),
                updatedAt: new \DateTimeImmutable(),
            ));

        $expense = $this->service->update($expenseDTO);

        $this->assertEquals($expenseDTO->amount()->getValue(), $expense->amount()->getValue());
        // Description should remain unchanged
        $this->assertEquals($existingExpense->description()->getValue(), $expense->description()->getValue());
    }

    #[Test]
    public function itShouldUpdateOnlyExpenseDescription(): void
    {
        $expenseDTO = new UpdateExpenseDTO(
            id:  new Id('a757d8e8-af2f-439d-b0de-c8dcd77d54e5'),
            userId: new Id('12345678-1234-1234-1234-123456789012'),
            description: new Description('New Expense description')
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->userId());

        $existingExpense = new Expense(
            user: new User(
                name: new Name('John Doe'),
                email: new Email('ilovelaravel@gmail.com'),
                password: new Password('P4sSW0rd!@#)'),
                id: $expenseDTO->userId()
            ),
            amount: new Amount('12'),
            description: new Description('Test Expense'),
            id: $expenseDTO->id()
        );

        $this->findOrFailExpenseByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->id())
            ->willReturn($existingExpense);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturn(new Expense(
                user: new User(
                    name: new Name('John Doe'),
                    email: new Email('ilovelaravel@gmail.com'),
                    password: new Password('P4sSW0rd!@#)'),
                    id: $expenseDTO->userId()
                ),
                amount: $existingExpense->amount(),
                description: $expenseDTO->description(),
                status: $existingExpense->status(),
                id: $existingExpense->id(),
                createdAt: $existingExpense->createdAt(),
                updatedAt: new \DateTimeImmutable(),
            ));

        $expense = $this->service->update($expenseDTO);

        $this->assertEquals($expenseDTO->description()->getValue(), $expense->description()->getValue());
        // Amount should remain unchanged
        $this->assertEquals($existingExpense->amount()->getValue(), $expense->amount()->getValue());
    }

    #[Test]
    public function itShouldUpdateExpenseAmountAndDescription(): void
    {
        $expenseDTO = new UpdateExpenseDTO(
            id:  new Id('a757d8e8-af2f-439d-b0de-c8dcd77d54e5'),
            userId: new Id('12345678-1234-1234-1234-123456789012'),
            amount: new Amount('1234'),
            description: new Description('New Expense description')
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->userId());

        $existingExpense = new Expense(
            user: new User(
                name: new Name('John Doe'),
                email: new Email('ilovelaravel@gmail.com'),
                password: new Password('P4sSW0rd!@#)'),
                id: $expenseDTO->userId()
            ),
            amount: new Amount('12'),
            description: new Description('Test Expense'),
            id: $expenseDTO->id()
        );

        $this->findOrFailExpenseByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($expenseDTO->id())
            ->willReturn($existingExpense);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturn(new Expense(
                user: new User(
                    name: new Name('John Doe'),
                    email: new Email('ilovelaravel@gmail.com'),
                    password: new Password('P4sSW0rd!@#)'),
                    id: $expenseDTO->userId()
                ),
                amount: $expenseDTO->amount(),
                description: $expenseDTO->description(),
                status: $existingExpense->status(),
                id: $existingExpense->id(),
                createdAt: $existingExpense->createdAt(),
                updatedAt: new \DateTimeImmutable(),
            ));

        $expense = $this->service->update($expenseDTO);

        $this->assertEquals($expenseDTO->amount()->getValue(), $expense->amount()->getValue());;
        $this->assertEquals($expenseDTO->description()->getValue(), $expense->description()->getValue());
    }
}
