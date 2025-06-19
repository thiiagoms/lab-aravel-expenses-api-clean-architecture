<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register\Interfaces;

use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Domain\User\ValueObjects\Email;

interface VerifyUserEmailIsAvailableInterface
{
    /**
     * @throws EmailAlreadyExistsException
     */
    public function verify(Email $email): void;
}
