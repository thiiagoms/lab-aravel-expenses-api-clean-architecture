<?php

declare(strict_types=1);

namespace Src\Domain\Repositories\User\Find;

use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

interface FindUserByIdRepositoryInterface
{
    public function find(Id $id): ?User;
}
