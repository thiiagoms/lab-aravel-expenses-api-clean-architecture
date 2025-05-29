<?php

declare(strict_types=1);

namespace Src\Domain\Repositories\User\Find;

use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;

interface FindUserByEmailRepositoryInterface
{
    public function find(Email $email): ?User;
}
