<?php

namespace Src\Domain\Repositories\Expense\Destroy;

use Src\Domain\ValueObjects\Id;

interface DestroyExpenseRepositoryInterface
{
    public function destroy(Id $id): bool;
}
