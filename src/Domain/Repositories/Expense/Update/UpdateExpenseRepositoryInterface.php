<?php

namespace Src\Domain\Repositories\Expense\Update;

use Src\Domain\Expense\Entities\Expense;

interface UpdateExpenseRepositoryInterface
{
    public function update(Expense $expense): Expense;
}
