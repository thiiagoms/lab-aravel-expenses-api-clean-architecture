<?php

declare(strict_types=1);

namespace Src\Domain\Repositories\Expense\Register;

use Src\Domain\Expense\Entities\Expense;

interface RegisterExpenseRepositoryInterface
{
    public function save(Expense $expense): Expense;
}
