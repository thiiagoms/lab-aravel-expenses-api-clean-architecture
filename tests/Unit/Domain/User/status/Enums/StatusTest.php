<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\status\Enums;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\User\Status\Enums\Status;

class StatusTest extends TestCase
{
    #[Test]
    public function is_awaiting_activation()
    {
        $status = Status::AWAITING_ACTIVATION;
        $this->assertTrue($status->isAwaitingActivation());
        $this->assertFalse($status->isActive());
        $this->assertFalse($status->isSuspended());
        $this->assertFalse($status->isBanned());
    }

    #[Test]
    public function is_active()
    {
        $status = Status::ACTIVE;
        $this->assertTrue($status->isActive());
        $this->assertFalse($status->isAwaitingActivation());
        $this->assertFalse($status->isSuspended());
        $this->assertFalse($status->isBanned());
    }

    #[Test]
    public function is_suspended()
    {
        $status = Status::SUSPENDED;
        $this->assertTrue($status->isSuspended());
        $this->assertFalse($status->isAwaitingActivation());
        $this->assertFalse($status->isActive());
        $this->assertFalse($status->isBanned());
    }

    #[Test]
    public function is_banned()
    {
        $status = Status::BANNED;
        $this->assertTrue($status->isBanned());
        $this->assertFalse($status->isAwaitingActivation());
        $this->assertFalse($status->isActive());
        $this->assertFalse($status->isSuspended());
    }

    #[Test]
    public function enum_values()
    {
        $this->assertEquals('awaiting_activation', Status::AWAITING_ACTIVATION->value);
        $this->assertEquals('active', Status::ACTIVE->value);
        $this->assertEquals('suspended', Status::SUSPENDED->value);
        $this->assertEquals('banned', Status::BANNED->value);
    }
}
