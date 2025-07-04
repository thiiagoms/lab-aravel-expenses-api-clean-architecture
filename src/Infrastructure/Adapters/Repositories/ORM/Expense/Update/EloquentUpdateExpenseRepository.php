<?php

namespace Src\Infrastructure\Adapters\Repositories\ORM\Expense\Update;

use Src\Domain\Expense\Entities\Expense;
use Src\Domain\Repositories\Expense\Update\UpdateExpenseRepositoryInterface;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;

class EloquentUpdateExpenseRepository extends BaseEloquentRepository implements UpdateExpenseRepositoryInterface
{
    /** @var LaravelExpenseModel */
    protected $model = LaravelExpenseModel::class;

    public function update(Expense $expense): Expense
    {
        $affected = $this->model
            ->where('id', $expense->id()?->getValue())
            ?->update($expense->toArray());

        if ($affected === 0) {
            throw new \InvalidArgumentException('Expense not found');
        }

        return $expense;
    }
}
