<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Auth\Token\Interfaces;

use Src\Domain\Auth\ValueObjects\Token;
use Src\Domain\User\Entities\User;

interface GenerateTokenInterface
{
    public function create(User $user): Token;
}
