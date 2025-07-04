<?php

namespace Src\Infrastructure\Adapters\Mappers\Expense;

use Carbon\Carbon;
use Src\Domain\Expense\Entities\Expense;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;

class ExpenseEntityToExpenseModelMapper
{
    public static function map(Expense $expense, LaravelExpenseModel $model): LaravelExpenseModel
    {
        $model ??= new LaravelExpenseModel;

        $model->id = $expense->id();
        $model->user_id = $expense->user()->id()->getValue();
        $model->amount = $expense->amount();
        $model->description = $expense->description();
        $model->status = $expense->status();
        $model->created_at = Carbon::createFromImmutable($expense->createdAt());
        $model->updated_at = Carbon::createFromImmutable($expense->updatedAt());

        return $model;
    }
}
