<?php

declare(strict_types=1);

namespace Src\Domain\Expense\Status\Exceptions;

use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Expense\Status\Enums\Status;

final class InvalidStatusTransitionException extends \DomainException
{
    public function __construct(Status $from, Status $to, Expense $expense)
    {
        $message = sprintf(
            "Invalid status transition from '%s' to '%s' on expense: '%s'.",
            $from->value,
            $to->value,
            $expense->id()->getValue()
        );

        parent::__construct($message);
    }
}
