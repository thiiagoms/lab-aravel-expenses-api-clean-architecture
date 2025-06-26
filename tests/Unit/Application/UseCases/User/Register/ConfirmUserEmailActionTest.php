<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\User\Register;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Application\UseCases\User\Register\ConfirmUserEmailAction;
use Src\Application\UseCases\User\Register\Services\ConfirmUserEmailService;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;
use Src\Domain\Repositories\User\Register\ConfirmUserEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

final class ConfirmUserEmailActionTest extends TestCase
{
    private Id $id;

    private FindOrFailUserByIdService|MockObject $findOrFailUserByIdService;

    private ConfirmUserEmailService $confirmUserEmailService;

    private ConfirmUserEmailRepositoryInterface|MockObject $confirmUserEmailRepository;

    private TransactionManagerInterface|MockObject $transactionManager;

    private ConfirmUserEmailAction $action;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->id = new Id(fake()->uuid());

        $this->findOrFailUserByIdService = $this->createMock(FindOrFailUserByIdService::class);

        $this->confirmUserEmailService = new ConfirmUserEmailService;

        $this->confirmUserEmailRepository = $this->createMock(ConfirmUserEmailRepositoryInterface::class);

        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->action = new ConfirmUserEmailAction(
            confirmUserEmailService: $this->confirmUserEmailService,
            findOrFailUserService: $this->findOrFailUserByIdService,
            transactionManager: $this->transactionManager,
            confirmUserEmailRepository: $this->confirmUserEmailRepository
        );
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_should_throws_exception_when_user_not_found(): void
    {
        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willThrowException(new UserNotFoundException('User not found'));

        $this->transactionManager
            ->expects($this->any())
            ->method('makeTransaction');

        $this->confirmUserEmailRepository
            ->expects($this->any())
            ->method('confirm');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->action->handle($this->id);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_should_returns_user_if_email_already_confirmed(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SsW0rd!@#D'),
            id: $this->id,
            status: new AwaitingActivation,
            emailConfirmedAt: new \DateTimeImmutable,
            createdAt: new \DateTimeImmutable,
            updatedAt: new \DateTimeImmutable
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn($user);

        $this->transactionManager
            ->expects($this->any())
            ->method('makeTransaction');

        $this->confirmUserEmailRepository
            ->expects($this->any())
            ->method('confirm');
        $result = $this->action->handle($this->id);

        $this->assertEquals($user, $result);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_should_confirms_user_email_and_activates_user(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SsW0rd!@#D'),
            id: $this->id,
            status: new AwaitingActivation,
            emailConfirmedAt: null,
            createdAt: new \DateTimeImmutable,
            updatedAt: new \DateTimeImmutable
        );

        $this->findOrFailUserByIdService
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->id)
            ->willReturn($user);

        $this->confirmUserEmailRepository
            ->expects($this->once())
            ->method('confirm')
            ->with($this->callback(function (User $user): bool {
                return $user->status() instanceof Active
                    && $user->emailConfirmedAt() instanceof \DateTimeImmutable;
            }))
            ->willReturn(true);

        $this->transactionManager
            ->expects($this->once())
            ->method('makeTransaction')
            ->willReturnCallback(fn (\Closure $callback): User => $callback());

        $result = $this->action->handle($this->id);

        $this->assertEquals(new Active, $result->status());
        $this->assertNotNull($result->emailConfirmedAt());
        $this->assertTrue($result->isEmailAlreadyConfirmed());
    }
}
