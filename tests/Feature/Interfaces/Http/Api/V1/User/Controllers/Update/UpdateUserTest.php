<?php

namespace Feature\Interfaces\Http\Api\V1\User\Controllers\Update;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Domain\User\ValueObjects\Email;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use DatabaseTransactions;

    private const string UPDATE_USER_ENDPOINT = '/api/v1/user/profile';

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_is_not_authenticated_tries_to_update_profile(): void
    {
        $this
            ->patchJson(self::UPDATE_USER_ENDPOINT, [])
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'Unauthenticated.')
            );
    }

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
            ->patchJson(self::UPDATE_USER_ENDPOINT, [])
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
            ->patchJson(self::UPDATE_USER_ENDPOINT, [])
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'This action is unauthorized.')
            );
    }

    public static function invalidUserNameProvider(): array
    {
        return [
            'should return name min length message when name is lower than min length' => [
                str_repeat('#', 2),
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.name',
                        'error.name.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.name' => 'array',
                        'error.name.0' => 'string',
                    ])
                    ->where('error.name.0', 'Name must be between 3 and 150 characters and contains only letters.'),
            ],
            'should return name max length message when name length is higher than max length' => [
                implode(',', fake()->paragraphs(151)),
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.name',
                        'error.name.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.name' => 'array',
                        'error.name.0' => 'string',
                    ])
                    ->where('error.name.0', 'Name must be between 3 and 150 characters and contains only letters.'),
            ],
            'should return name type message when name is not a string' => [
                (float) fake()->randomFloat(),
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.name',
                        'error.name.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.name' => 'array',
                        'error.name.0' => 'string',
                    ])
                    ->where('error.name.0', 'Name must be between 3 and 150 characters and contains only letters.'),
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidUserNameProvider')]
    public function it_should_validate_user_name_when_name_is_provided(string|float $name, \Closure $response): void
    {
        $user = LaravelUserModel::factory()->createOne();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->actingAs($user)
            ->patchJson(self::UPDATE_USER_ENDPOINT, ['name' => $name])
            ->assertBadRequest()
            ->assertJson($response);
    }

    public static function invalidUserEmailProvider(): array
    {
        return [
            'should return email is invalid message when email is not a valid email' => [
                fake()->name(),
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.email',
                        'error.email.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.email' => 'array',
                        'error.email.0' => 'string',
                    ])
                    ->where('error.email.0', 'The provided email address is not valid. Please enter a valid email.'),
            ],
            'should return email already exists message when email already exists' => [
                'ilovelaravel@gmail.com',
                fn (AssertableJson $json): AssertableJson => $json
                    ->has('message')
                    ->whereType('message', 'string')
                    ->where('message', 'User with provided e-mail already exists'),
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidUserEmailProvider')]
    public function it_should_validate_user_email_when_email_is_provided(string $email, \Closure $response): void
    {
        LaravelUserModel::factory()->createOne(['email' => new Email('ilovelaravel@gmail.com')]);

        $user = LaravelUserModel::factory()->createOne();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->actingAs($user)
            ->patchJson(self::UPDATE_USER_ENDPOINT, ['email' => $email])
            ->assertJson($response);
    }

    public static function invalidUserPasswordProvider(): array
    {
        return [
            'should return password min length message when password is less than 8 characters' => [
                'p4sS!',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.password',
                        'error.password.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.password' => 'array',
                        'error.password.0' => 'string',
                    ])
                    ->where(
                        'error.password.0',
                        'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.'
                    ),
            ],
            'should return password numbers message when password does not contain at least one number' => [
                'pAssssssssS!',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.password',
                        'error.password.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.password' => 'array',
                        'error.password.0' => 'string',
                    ])
                    ->where(
                        'error.password.0',
                        'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.'
                    ),
            ],
            'should return password symbols message when password does not contain at least one symbol' => [
                'pAsssssss12sSD',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.password',
                        'error.password.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.password' => 'array',
                        'error.password.0' => 'string',
                    ])
                    ->where(
                        'error.password.0',
                        'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.'
                    ),
            ],
            'should return password mixed case message when password does not contain at least one lower and upper case letter' => [
                'p4sssssss12s@ad',
                fn (AssertableJson $json): AssertableJson => $json
                    ->hasAll([
                        'error',
                        'error.password',
                        'error.password.0',
                    ])
                    ->whereAllType([
                        'error' => 'array',
                        'error.password' => 'array',
                        'error.password.0' => 'string',
                    ])
                    ->where(
                        'error.password.0',
                        'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.'
                    ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidUserPasswordProvider')]
    public function it_should_validate_user_password_when_password_is_provided(string $password, \Closure $response): void
    {
        $user = LaravelUserModel::factory()->createOne();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->actingAs($user)
            ->patchJson(self::UPDATE_USER_ENDPOINT, ['password' => $password])
            ->assertJson($response);
    }

    #[Test]
    public function it_should_update_only_user_name_when_name_is_provided(): void
    {
        $user = LaravelUserModel::factory()->createOne();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->actingAs($user)
            ->patchJson(self::UPDATE_USER_ENDPOINT, ['name' => 'New Name'])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->hasAll([
                    'data',
                    'data.id',
                    'data.name',
                    'data.email',
                    'data.created_at',
                    'data.updated_at',
                ])
                ->whereAllType([
                    'data' => 'array',
                    'data.id' => 'string',
                    'data.name' => 'string',
                    'data.email' => 'string',
                    'data.created_at' => 'string',
                    'data.updated_at' => 'string',
                ])
                ->whereAll([
                    'data.id' => $user->id->getValue(),
                    'data.name' => 'New Name',
                    'data.email' => $user->email->getValue(),
                ])
            );
    }

    #[Test]
    public function it_should_update_only_user_email_when_email_is_provided(): void
    {
        $user = LaravelUserModel::factory()->createOne();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->actingAs($user)
            ->patchJson(self::UPDATE_USER_ENDPOINT, ['email' => 'ilovelaravel@gmail.com'])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->hasAll([
                    'data',
                    'data.id',
                    'data.name',
                    'data.email',
                    'data.created_at',
                    'data.updated_at',
                ])
                ->whereAllType([
                    'data' => 'array',
                    'data.id' => 'string',
                    'data.name' => 'string',
                    'data.email' => 'string',
                    'data.created_at' => 'string',
                    'data.updated_at' => 'string',
                ])
                ->whereAll([
                    'data.id' => $user->id->getValue(),
                    'data.name' => $user->name->getValue(),
                    'data.email' => 'ilovelaravel@gmail.com',
                ])
            );
    }

    #[Test]
    public function it_should_update_only_user_password_when_password_is_provided(): void
    {
        $user = LaravelUserModel::factory()->createOne();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->actingAs($user)
            ->patchJson(self::UPDATE_USER_ENDPOINT, ['password' => 'P4SsW0rdQWE!@SAD_@#!@#DA'])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->hasAll([
                    'data',
                    'data.id',
                    'data.name',
                    'data.email',
                    'data.created_at',
                    'data.updated_at',
                ])
                ->whereAllType([
                    'data' => 'array',
                    'data.id' => 'string',
                    'data.name' => 'string',
                    'data.email' => 'string',
                    'data.created_at' => 'string',
                    'data.updated_at' => 'string',
                ])
                ->whereAll([
                    'data.id' => $user->id->getValue(),
                    'data.name' => $user->name->getValue(),
                    'data.email' => $user->email->getValue(),
                ])
            );

        // Verify that the password was updated
        $user = LaravelUserModel::find($user->id->getValue());

        $this->assertTrue($user->password->verifyPasswordMatch('P4SsW0rdQWE!@SAD_@#!@#DA'));
        $this->assertFalse($user->password->verifyPasswordMatch('@p5sSw0rd!'));
    }

    #[Test]
    public function it_should_update_user_name_and_email_and_password_when_all_fields_are_provided(): void
    {
        $user = LaravelUserModel::factory()->createOne();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->actingAs($user)
            ->putJson(self::UPDATE_USER_ENDPOINT, [
                'name' => 'New Name',
                'email' => 'ilovelaravel@gmail.com',
                'password' => 'P4SsW0rdQWE!@SAD_@#!@#DA',
            ])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->hasAll([
                    'data',
                    'data.id',
                    'data.name',
                    'data.email',
                    'data.created_at',
                    'data.updated_at',
                ])
                ->whereAllType([
                    'data' => 'array',
                    'data.id' => 'string',
                    'data.name' => 'string',
                    'data.email' => 'string',
                    'data.created_at' => 'string',
                    'data.updated_at' => 'string',
                ])
                ->whereAll([
                    'data.id' => $user->id->getValue(),
                    'data.name' => 'New Name',
                    'data.email' => 'ilovelaravel@gmail.com',
                ])
            );

        // Verify that the password was updated
        $user = LaravelUserModel::find($user->id->getValue());

        $this->assertTrue($user->password->verifyPasswordMatch('P4SsW0rdQWE!@SAD_@#!@#DA'));
        $this->assertFalse($user->password->verifyPasswordMatch('@p5sSw0rd!'));
    }
}
