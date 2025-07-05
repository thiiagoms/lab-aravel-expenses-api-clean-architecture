<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Expense\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\Expense\Services\CanUserRegisterExpenseService;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use Src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;

class CanUserRegisterExpenseServiceTest extends TestCase
{
    private CanUserRegisterExpenseService $canUserRegisterExpenseService;

    protected function setUp(): void
    {
        $this->canUserRegisterExpenseService = new CanUserRegisterExpenseService;
    }

    public static function statusProvider(): array
    {
        return [
            [new AwaitingActivation],
            [new Suspended],
            [new Banned],
        ];
    }

    #[Test]
    #[DataProvider('statusProvider')]
    public function it_should_return_false_when_user_is_not_active(StatusInterface $status): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSWo-!@#S_1234'),
            status: $status,
        );

        $result = $this->canUserRegisterExpenseService->handle($user);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_should_return_false_even_user_is_active_but_email_is_not_confirmed(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSWo-!@#S_1234'),
            status: new Active,
        );

        $result = $this->canUserRegisterExpenseService->handle($user);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_should_return_true_when_user_is_active_and_email_is_confirmed(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4SSWo-!@#S_1234'),
            status: new Active,
            emailConfirmedAt: new \DateTimeImmutable
        );

        $result = $this->canUserRegisterExpenseService->handle($user);

        $this->assertTrue($result);
    }
}
