<?php

namespace Feature\Interfaces\Http\Api\V1\Expense\Controllers\Register;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class RegisterExpenseTest extends TestCase
{
    use DatabaseTransactions;

    private const string REGISTER_EXPENSE_ENDPOINT = '/api/v1/expense';

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        Queue::fake();
    }

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_retrieve_his_data(): void
    {
        $this
            ->postJson(self::REGISTER_EXPENSE_ENDPOINT)
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'Unauthenticated.')
            );
    }

    /**
     * @return StatusInterface[]
     */
    public static function invalidUserStatusProvider(): array
    {
        return [
            [new AwaitingActivation],
            [new Suspended],
            [new Banned],
        ];
    }

    #[Test]
    #[DataProvider('invalidUserStatusProvider')]
    public function it_should_return_authenticated_when_user_is_not_active(StatusInterface $status): void
    {
        $user = LaravelUserModel::factory()->create(['status' => $status]);

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->postJson(self::REGISTER_EXPENSE_ENDPOINT)
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'This action is unauthorized.')
            );
    }

    #[Test]
    public function it_should_return_authenticated_when_user_is_active_but_email_is_not_confirmed(): void
    {
        $user = LaravelUserModel::factory()->create(['email_verified_at' => null]);

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->postJson(self::REGISTER_EXPENSE_ENDPOINT)
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'This action is unauthorized.')
            );
    }

    #[Test]
    public function it_should_return_fields_are_required_when_entire_payload_is_empty(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->postJson(self::REGISTER_EXPENSE_ENDPOINT, [])
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->hasAll([
                    'error',
                    'error.amount',
                    'error.description',
                ])
                ->whereAllType([
                    'error' => 'array',
                    'error.amount' => 'array',
                    'error.amount.0' => 'string',
                    'error.description' => 'array',
                    'error.description.0' => 'string',
                ])
                ->whereAll([
                    'error.amount.0' => 'Invalid amount provided. The expense amount must have a positive numeric value with up to two decimal places.',
                    'error.description.0' => 'Description cannot be empty and must be at least 3 characters long.',
                ])
            );
    }

    public static function invalidAmountProvider(): array
    {
        return [
            'should validate amount when amount value is empty' => [
                '',
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
    public function it_should_validate_amount(string $amount, \Closure $response): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->postJson(self::REGISTER_EXPENSE_ENDPOINT, ['amount' => $amount, 'description' => fake()->text()])
            ->assertBadRequest()
            ->assertJson($response);
    }

    public static function invalidDescriptionProvider(): array
    {
        return [
            'should validate description when description value is empty' => [
                '',
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
    public function it_should_validate_description(string $description, \Closure $response): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->postJson(self::REGISTER_EXPENSE_ENDPOINT, ['amount' => '12', 'description' => $description])
            ->assertBadRequest()
            ->assertJson($response);
    }

    #[Test]
    public function it_should_create_expense(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->postJson(self::REGISTER_EXPENSE_ENDPOINT, [
                'amount' => '12200',
                'description' => 'Expense example description',
            ])
            ->assertCreated()
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
                    'data.amount' => 122000000,
                    'data.description' => 'Expense example description',
                ])
            );

        Queue::assertNothingPushed();
    }
}
