<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Status\Factory;

use Src\Domain\Expense\Status\Approve;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\Expense\Status\Pending;
use Src\Domain\Expense\Status\Rejected;

abstract class StatusFactory
{
    public static function build(Status $status): StatusInterface
    {
        return match ($status) {
            Status::PENDING => new Pending,
            Status::APPROVED => new Approve,
            Status::REJECTED => new Rejected,
        };
    }
}
