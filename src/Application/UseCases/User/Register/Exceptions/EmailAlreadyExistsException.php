<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register\Exceptions;

use Src\Domain\User\ValueObjects\Email;
use Throwable;

final class EmailAlreadyExistsException extends \DomainException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(Email $email): self
    {
        return new self("User with e-mail '{$email->getValue()}' already exists");
    }
}
