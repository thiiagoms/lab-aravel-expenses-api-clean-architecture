<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Expense\Status\Enums;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\Expense\Status\Enums\Status;

class StatusTest extends TestCase
{
    #[Test]
    public function it_should_return_true_when_is_pending()
    {
        $status = Status::PENDING;
        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isApproved());
        $this->assertFalse($status->isRejected());
    }

    #[Test]
    public function it_should_return_true_when_is_approved()
    {
        $status = Status::APPROVED;
        $this->assertTrue($status->isApproved());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isRejected());
    }

    #[Test]
    public function it_should_return_true_when_is_rejected()
    {
        $status = Status::REJECTED;
        $this->assertTrue($status->isRejected());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isApproved());
    }

    #[Test]
    public function it_should_return_enum_values()
    {
        $this->assertEquals('pending', Status::PENDING->value);
        $this->assertEquals('approved', Status::APPROVED->value);
        $this->assertEquals('rejected', Status::REJECTED->value);
    }
}
