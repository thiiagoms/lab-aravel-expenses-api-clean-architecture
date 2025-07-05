<?php

namespace Src\Application\UseCases\Expense\Update\Exceptions;

class ExpenseCanNotBeUpdatedException extends \DomainException
{
    public static function create(string $message): self
    {
        return new self($message);
    }
}
