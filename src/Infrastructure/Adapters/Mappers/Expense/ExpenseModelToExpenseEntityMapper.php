<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Mappers\Expense;

use Src\Domain\Expense\Entities\Expense;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;

abstract class ExpenseModelToExpenseEntityMapper
{
    public static function map(LaravelExpenseModel $model): Expense
    {
        return new Expense(
            user: UserModelToUserEntityMapper::map($model->user),
            amount: $model->amount,
            description: $model->description,
            status: $model->status,
            id: $model->id,
            createdAt: $model->created_at->toDateTimeImmutable(),
            updatedAt: $model->updated_at->toDateTimeImmutable()
        );
    }
}
