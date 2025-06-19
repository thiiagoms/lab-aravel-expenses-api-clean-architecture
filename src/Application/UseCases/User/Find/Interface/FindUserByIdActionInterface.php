<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Find\Interface;

use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

interface FindUserByIdActionInterface
{
    public function handle(Id $id): User;
}
