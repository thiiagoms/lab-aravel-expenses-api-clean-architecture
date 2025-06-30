<?php

declare(strict_types=1);

namespace Src\Domain\Repositories\Expense\Find;

use Src\Domain\Expense\Entities\Expense;
use Src\Domain\ValueObjects\Id;

interface FindExpenseByIdRepositoryInterface
{
    public function find(Id $id): ?Expense;
}
