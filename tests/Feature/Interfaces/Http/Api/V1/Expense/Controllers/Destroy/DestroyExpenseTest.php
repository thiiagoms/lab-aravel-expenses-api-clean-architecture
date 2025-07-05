<?php

namespace Feature\Interfaces\Http\Api\V1\Expense\Controllers\Destroy;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class DestroyExpenseTest extends TestCase
{
    use DatabaseTransactions;

    private const string DESTROY_EXPENSE_ENDPOINT = '/api/v1/expense/';

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_destroy_expense_that_does_not_exists(): void
    {
        $this
            ->deleteJson(self::DESTROY_EXPENSE_ENDPOINT.fake()->uuid())
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'Unauthenticated.')
            );
    }

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_destroy_expense(): void
    {
        $expense = LaravelExpenseModel::factory()->createOne();

        $this
            ->deleteJson(self::DESTROY_EXPENSE_ENDPOINT.$expense->id->getValue())
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'Unauthenticated.')
            );
    }

    #[Test]
    public function it_should_return_not_found_message_when_expense_does_not_exists(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->deleteJson(self::DESTROY_EXPENSE_ENDPOINT.fake()->uuid())
            ->assertNotFound()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'resource not found')
            );
    }

    #[Test]
    public function it_should_return_authorization_message_when_user_is_authenticated_but_does_not_own_the_expense(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $expense = LaravelExpenseModel::factory()->createOne();

        $this
            ->deleteJson(self::DESTROY_EXPENSE_ENDPOINT.$expense->id->getValue())
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'You do not have permission to view this expense.')
            );
    }

    #[Test]
    public function it_should_destroy_user_expense(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $expense = LaravelExpenseModel::factory()->createOne(['user_id' => $user->id->getValue()]);

        $this
            ->deleteJson(self::DESTROY_EXPENSE_ENDPOINT.$expense->id->getValue())
            ->assertNoContent();
    }
}
