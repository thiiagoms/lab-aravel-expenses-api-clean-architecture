<?php

namespace Src\Infrastructure\Framework\Laravel\Policies;

use Illuminate\Auth\Access\AuthorizationException;
use Src\Domain\Expense\Entities\Expense;
use Src\Infrastructure\Adapters\Mappers\Expense\ExpenseModelToExpenseEntityMapper;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

class ExpensePolicy
{
    /**
     * @throws AuthorizationException
     */
    public function view(LaravelUserModel $userModel, LaravelExpenseModel $expense): bool
    {
        $expense = ExpenseModelToExpenseEntityMapper::map($expense);

        return $this->verifyExpenseIsOwner($userModel, $expense);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(LaravelUserModel $userModel, LaravelExpenseModel $expense): bool
    {
        $expense = ExpenseModelToExpenseEntityMapper::map($expense);

        return $this->verifyExpenseIsOwner($userModel, $expense);
    }

    /**
     * @throws AuthorizationException
     */
    public function delete(LaravelUserModel $userModel, LaravelExpenseModel $expense): bool
    {
        $expense = ExpenseModelToExpenseEntityMapper::map($expense);

        return $this->verifyExpenseIsOwner($userModel, $expense);
    }

    /**
     * @throws AuthorizationException
     */
    public function verifyExpenseIsOwner(LaravelUserModel $userModel, Expense $expense): bool
    {
        $user = UserModelToUserEntityMapper::map($userModel);

        if ($user->id()->getValue() === $expense->user()->id()->getValue()) {
            return true;
        }

        throw new AuthorizationException('You do not have permission to view this expense.');
    }
}
