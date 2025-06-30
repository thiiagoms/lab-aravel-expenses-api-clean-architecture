<?php

namespace Src\Infrastructure\Adapters\Repositories\ORM\Expense\Find;

use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Repositories\Expense\Find\FindExpenseByIdRepositoryInterface;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Adapters\Mappers\Expense\ExpenseModelToExpenseEntityMapper;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;

final class EloquentFindExpenseByIdRepository extends BaseEloquentRepository implements FindExpenseByIdRepositoryInterface
{
    /** @var LaravelExpenseModel */
    protected $model = LaravelExpenseModel::class;

    public function find(Id $id): ?Expense
    {
        $expense = $this->model->find($id->getValue());

        if ($expense === null) {
            return null;
        }

        return ExpenseModelToExpenseEntityMapper::map($expense);
    }
}
