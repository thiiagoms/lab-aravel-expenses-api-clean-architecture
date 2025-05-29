<?php

declare(strict_types=1);

namespace Src\Domain\User\Role\Exceptions;

use Src\Domain\User\Entities\User;
use Src\Domain\User\Role\Enums\Role;

final class InvalidRoleTransitionException extends \DomainException
{
    public function __construct(Role $from, Role $to, User $user)
    {
        $message = "Invalid role transition from '{$from->value}' to '{$to->value}' on user '{$user->email()->getValue()}'";
        parent::__construct($message);
    }
}
