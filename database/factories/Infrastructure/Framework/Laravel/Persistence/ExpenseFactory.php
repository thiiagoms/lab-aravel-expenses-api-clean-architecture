<?php

namespace Database\Factories\Infrastructure\Framework\Laravel\Persistence;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

/**
 * @extends Factory<LaravelExpenseModel>
 */
class ExpenseFactory extends Factory
{
    protected $model = LaravelExpenseModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = LaravelUserModel::factory()->createOne();

        return [
            'id' => new Id(fake()->uuid),
            'user_id' => $user->id->getValue(),
            'amount' => intval(round(rand(1, 1000))),
            'description' => fake()->text(),
            'status' => Status::APPROVED->value,
        ];
    }
}
