<?php

namespace Feature\Interfaces\Http\Api\V1\Expense\Controllers\Retrieve;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class RetrieveExpenseTest extends TestCase
{
    use DatabaseTransactions;

    private const string RETRIEVE_EXPENSE_ENDPOINT = '/api/v1/expense/';

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_retrieve_expense_that_does_not_exists(): void
    {
        $this
            ->getJson(self::RETRIEVE_EXPENSE_ENDPOINT.fake()->uuid())
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'Unauthenticated.')
            );
    }

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_retrieve_expense(): void
    {
        $expense = LaravelExpenseModel::factory()->createOne();

        $this
            ->getJson(self::RETRIEVE_EXPENSE_ENDPOINT.$expense->id->getValue())
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
            ->getJson(self::RETRIEVE_EXPENSE_ENDPOINT.fake()->uuid())
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Expense not found')
            );
    }

    #[Test]
    public function it_should_return_authorization_message_when_user_is_authenticated_but_does_not_own_the_expense(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $expense = LaravelExpenseModel::factory()->createOne();

        $this
            ->getJson(self::RETRIEVE_EXPENSE_ENDPOINT.$expense->id->getValue())
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'You do not have permission to view this expense.')
            );
    }

    #[Test]
    public function it_should_return_user_authenticated_expense(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $expense = LaravelExpenseModel::factory()->createOne(['user_id' => $user->id->getValue()]);

        $this
            ->getJson(self::RETRIEVE_EXPENSE_ENDPOINT.$expense->id->getValue())
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->hasAll([
                    'data',
                    'data.id',
                    'data.amount',
                    'data.description',
                    'data.created_at',
                    'data.updated_at',
                ])
                ->whereAllType([
                    'data' => 'array',
                    'data.id' => 'string',
                    'data.amount' => 'integer',
                    'data.description' => 'string',
                    'data.created_at' => 'string',
                    'data.updated_at' => 'string',
                ])
                ->whereAll([
                    'data.id' => $expense->id->getValue(),
                    'data.amount' => $expense->amount->getValue(),
                    'data.description' => $expense->description->getValue(),
                ])
            );
    }
}
