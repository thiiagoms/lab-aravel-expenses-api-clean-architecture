<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Expense\Register\Exceptions;

class UserCannotRegisterExpenseException extends \DomainException
{
    public static function create(): self
    {
        $message = 'User cannot register expense';

        return new self($message);
    }
}
