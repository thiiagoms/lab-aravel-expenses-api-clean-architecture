<?php

namespace Tests\Unit\Domain\Expense\Status\Factory;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\Expense\Status\Approve;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Domain\Expense\Status\Factory\StatusFactory;
use Src\Domain\Expense\Status\Pending;
use Src\Domain\Expense\Status\Rejected;

class StatusFactoryTest extends TestCase
{
    #[Test]
    public function it_should_return_pending_instance(): void
    {
        $status = StatusFactory::build(Status::PENDING);

        $this->assertInstanceOf(Pending::class, $status);
    }

    #[Test]
    public function it_should_return_approved_instance(): void
    {
        $status = StatusFactory::build(Status::APPROVED);

        $this->assertInstanceOf(Approve::class, $status);
    }

    #[Test]
    public function it_should_return_rejected_instance(): void
    {
        $status = StatusFactory::build(Status::REJECTED);

        $this->assertInstanceOf(Rejected::class, $status);
    }

    #[Test]
    public function it_should_return_different_instances_for_different_statuses(): void
    {
        $pending = StatusFactory::build(Status::PENDING);
        $approved = StatusFactory::build(Status::APPROVED);
        $rejected = StatusFactory::build(Status::REJECTED);

        $this->assertNotEquals(spl_object_id($pending), spl_object_id($approved));
        $this->assertNotEquals(spl_object_id($pending), spl_object_id($rejected));
        $this->assertNotEquals(spl_object_id($approved), spl_object_id($rejected));
    }
}
