<?php

namespace Src\Application\UseCases\Expense\Exceptions;

class ExpenseNotFoundException extends \DomainException
{
    public static function create(): self
    {
        return new self('Expense not found');
    }
}
