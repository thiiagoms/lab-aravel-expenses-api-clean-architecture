<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Auth\Authenticate\Interfaces;

use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Domain\User\Entities\User;

interface CheckUserCredentialsInterface
{
    public function validate(AuthenticateDTO $dto): bool;

    public function getAuthenticatedUser(): ?User;
}
