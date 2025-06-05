<?php

declare(strict_types=1);

namespace Src\Domain\User\Status\Exceptions;

use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Enums\Status;

final class InvalidStatusTransitionException extends \DomainException
{
    public function __construct(Status $from, Status $to, User $user)
    {
        $message = "Invalid status transition from '{$from->value}' to '{$to->value}' on user: '{$user->email()->getValue()}'.";
        parent::__construct($message);
    }
}
