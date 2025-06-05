<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Exceptions;

class UserNotFoundException extends \DomainException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(string $message): self
    {
        return new self($message);
    }
}
