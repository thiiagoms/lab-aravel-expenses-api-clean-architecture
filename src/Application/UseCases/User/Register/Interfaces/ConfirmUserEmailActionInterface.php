<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register\Interfaces;

use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

interface ConfirmUserEmailActionInterface
{
    public function handle(Id $id): User;
}
