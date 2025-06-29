<?php

namespace Src\Infrastructure\Adapters\Repositories\ORM\Expense\Register;

use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Repositories\Expense\Register\RegisterExpenseRepositoryInterface;
use Src\Infrastructure\Adapters\Mappers\Expense\ExpenseModelToExpenseEntityMapper;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;

final class EloquentRegisterExpenseRepository extends BaseEloquentRepository implements RegisterExpenseRepositoryInterface
{
    /** @var LaravelExpenseModel */
    protected $model = LaravelExpenseModel::class;

    public function save(Expense $expense): Expense
    {
        $expenseModel = $this->model->create($expense->toArray());

        return ExpenseModelToExpenseEntityMapper::map($expenseModel);
    }
}
