<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM\Expense\Destroy;

use Src\Domain\Repositories\Expense\Destroy\DestroyExpenseRepositoryInterface;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;

class EloquentDestroyExpenseRepository extends BaseEloquentRepository implements DestroyExpenseRepositoryInterface
{
    /** @var LaravelExpenseModel */
    protected $model = LaravelExpenseModel::class;

    public function destroy(Id $id): bool
    {
        return (bool) $this->model->destroy($id->getValue());
    }
}
