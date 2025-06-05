<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Status\Factory;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\Status\Factory\StatusFactory;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use Src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;

class StatusFactoryTest extends TestCase
{
    #[Test]
    public function it_returns_awaiting_activation_instance(): void
    {
        $status = StatusFactory::build(Status::AWAITING_ACTIVATION);
        $this->assertInstanceOf(AwaitingActivation::class, $status);
        $this->assertInstanceOf(StatusInterface::class, $status);
    }

    #[Test]
    public function it_returns_active_instance(): void
    {
        $status = StatusFactory::build(Status::ACTIVE);
        $this->assertInstanceOf(Active::class, $status);
        $this->assertInstanceOf(StatusInterface::class, $status);
    }

    #[Test]
    public function it_returns_suspended_instance(): void
    {
        $status = StatusFactory::build(Status::SUSPENDED);
        $this->assertInstanceOf(Suspended::class, $status);
        $this->assertInstanceOf(StatusInterface::class, $status);
    }

    #[Test]
    public function it_returns_banned_instance(): void
    {
        $status = StatusFactory::build(Status::BANNED);
        $this->assertInstanceOf(Banned::class, $status);
        $this->assertInstanceOf(StatusInterface::class, $status);
    }

    #[Test]
    public function it_returns_different_instances_for_different_statuses(): void
    {
        $active = StatusFactory::build(Status::ACTIVE);
        $banned = StatusFactory::build(Status::BANNED);
        $awaiting = StatusFactory::build(Status::AWAITING_ACTIVATION);
        $suspended = StatusFactory::build(Status::SUSPENDED);

        $this->assertNotEquals(spl_object_id($active), spl_object_id($banned));
        $this->assertNotEquals(spl_object_id($active), spl_object_id($awaiting));
        $this->assertNotEquals(spl_object_id($active), spl_object_id($suspended));
    }
}
