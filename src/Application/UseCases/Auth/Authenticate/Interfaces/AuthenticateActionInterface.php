<?php

namespace Src\Application\UseCases\Auth\Authenticate\Interfaces;

use Src\Application\UseCases\Auth\Authenticate\DTO\AuthenticateDTO;
use Src\Domain\Auth\ValueObjects\Token;

interface AuthenticateActionInterface
{
    public function handle(AuthenticateDTO $dto): Token;
}
