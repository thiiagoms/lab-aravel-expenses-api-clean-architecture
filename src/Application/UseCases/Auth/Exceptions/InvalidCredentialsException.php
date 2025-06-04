<?php

namespace Src\Application\UseCases\Auth\Exceptions;

class InvalidCredentialsException extends \DomainException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(): self
    {
        return new self(message: 'Authentication failed. Please check your credentials.');
    }
}
