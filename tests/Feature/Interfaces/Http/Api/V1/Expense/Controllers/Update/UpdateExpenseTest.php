<?php

namespace Feature\Interfaces\Http\Api\V1\Expense\Controllers\Update;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Infrastructure\Framework\Laravel\Persistence\Expense as LaravelExpenseModel;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class UpdateExpenseTest extends TestCase
{
    use DatabaseTransactions;

    private const string UPDATE_EXPENSE_ENDPOINT = '/api/v1/expense/';

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_update_expense_that_does_not_exists(): void
    {
        $this
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.fake()->uuid())
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'Unauthenticated.')
            );
    }

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_update_expense(): void
    {
        $expense = LaravelExpenseModel::factory()->createOne();

        $this
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.$expense->id->getValue())
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
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.fake()->uuid())
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
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.$expense->id->getValue())
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'You do not have permission to view this expense.')
            );
    }

    public static function invalidAmountProvider(): array
    {
        return [
            'should validate amount when amount value is not numeric' => [
                'abc',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.amount',
                        'error.amount.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.amount' => 'array',
                        'error.amount.0' => 'string',
                    ])
                    ->where(
                        'error.amount.0',
                        'Invalid amount provided. The expense amount must have a positive numeric value with up to two decimal places.'
                    ),
            ],
            'should validate amount when amount value is zero' => [
                '0',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.amount',
                        'error.amount.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.amount' => 'array',
                        'error.amount.0' => 'string',
                    ])
                    ->where(
                        'error.amount.0',
                        'Invalid amount provided. The expense amount must have a positive numeric value with up to two decimal places.'
                    ),
            ],
            'should validate amount when amount value is less than zero' => [
                '-1',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.amount',
                        'error.amount.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.amount' => 'array',
                        'error.amount.0' => 'string',
                    ])
                    ->where(
                        'error.amount.0',
                        'Invalid amount provided. The expense amount must have a positive numeric value with up to two decimal places.'
                    ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidAmountProvider')]
    public function it_should_validate_expense_amount_when_provided(string $amount, \Closure $response): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $expense = LaravelExpenseModel::factory()->createOne(['user_id' => $user->id->getValue()]);

        $this
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.$expense->id->getValue(), ['amount' => $amount])
            ->assertBadRequest()
            ->assertJson($response);
    }

    public static function invalidDescriptionProvider(): array
    {
        return [
            'should validate when description is less than min value' => [
                'ab',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.description',
                        'error.description.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.description' => 'array',
                        'error.description.0' => 'string',
                    ])
                    ->where(
                        'error.description.0',
                        'Description cannot be empty and must be at least 3 characters long.'
                    ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidDescriptionProvider')]
    public function it_should_validate_expense_description_when_provided(string $description, \Closure $response): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $expense = LaravelExpenseModel::factory()->createOne(['user_id' => $user->id->getValue()]);

        $this
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.$expense->id->getValue(), ['description' => $description])
            ->assertBadRequest()
            ->assertJson($response);
    }

    /**
     * @return string[]
     */
    public static function invalidExpenseStatusProvider(): array
    {
        return [
            [Status::APPROVED],
            [Status::REJECTED],
        ];
    }

    #[Test]
    #[DataProvider('invalidExpenseStatusProvider')]
    public function it_should_update_only_expense_with_pending_status(Status $status): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $expense = LaravelExpenseModel::factory()->createOne([
            'status' => $status->value,
            'user_id' => $user->id->getValue(),
        ]);

        $this
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.$expense->id->getValue(), ['amount' => '12.34'])
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Only pending expenses can be updated.')
            );
    }

    #[Test]
    public function it_should_update_only_expense_amount(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $existingExpense = LaravelExpenseModel::factory()->createOne(['user_id' => $user->id->getValue()]);

        $this
            ->patchJson(self::UPDATE_EXPENSE_ENDPOINT.$existingExpense->id->getValue(), ['amount' => '12.34'])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
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
                    'data.amount' => 1234,
                    'data.description' => $existingExpense->description->getValue(),
                ])
            );
    }

    #[Test]
    public function it_should_update_only_expense_description(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $existingExpense = LaravelExpenseModel::factory()->createOne(['user_id' => $user->id->getValue()]);

        $this
            ->patchJson(
                self::UPDATE_EXPENSE_ENDPOINT.$existingExpense->id->getValue(),
                ['description' => 'New expense description updated']
            )
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
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
                    'data.amount' => $existingExpense->amount->getValue(),
                    'data.description' => 'New expense description updated',
                ])
            );
    }

    #[Test]
    public function it_should_update_expense_amount_and_description(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $existingExpense = LaravelExpenseModel::factory()->createOne(['user_id' => $user->id->getValue()]);

        $this
            ->patchJson(
                self::UPDATE_EXPENSE_ENDPOINT.$existingExpense->id->getValue(),
                ['amount' => '12.34', 'description' => 'New expense description updated']
            )
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
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
                    'data.amount' => 1234,
                    'data.description' => 'New expense description updated',
                ])
            );
    }
}
