<?php

declare(strict_types=1);

namespace Feature\Interfaces\Http\Api\V1\Auth\Authenticate;

use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    use DatabaseTransactions;

    private const string AUTHENTICATE_USER_ENDPOINT = '/api/v1/auth/login';

    public static function invalidUserEmailProvider(): array
    {
        return [
            'email is required' => [
                '',
                fn (AssertableJson $json) => $json
                    ->hasAll(['error', 'error.email', 'error.email.0'])
                    ->whereAllType([
                        'error' => 'array',
                        'error.email' => 'array',
                        'error.email.0' => 'string',
                    ])
                    ->where('error.email.0', 'The provided email address is not valid. Please enter a valid email.'),
            ],
            'email is invalid' => [
                'invalid-name',
                fn (AssertableJson $json) => $json
                    ->hasAll(['error', 'error.email', 'error.email.0'])
                    ->whereAllType([
                        'error' => 'array',
                        'error.email' => 'array',
                        'error.email.0' => 'string',
                    ])
                    ->where('error.email.0', 'The provided email address is not valid. Please enter a valid email.'),
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidUserEmailProvider')]
    public function it_should_validate_user_email(string $email, Closure $response): void
    {
        $this
            ->postJson(self::AUTHENTICATE_USER_ENDPOINT, ['email' => $email, 'password' => '@p5sSw0rd!'])
            ->assertBadRequest()
            ->assertJson($response);
    }

    public static function invalidUserPasswordProvider(): array
    {
        $message = 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.';

        return [
            'password is required' => [
                '',
                fn (AssertableJson $json) => $json
                    ->hasAll(['error', 'error.password', 'error.password.0'])
                    ->whereAllType(['error' => 'array', 'error.password' => 'array', 'error.password.0' => 'string'])
                    ->where('error.password.0', $message),
            ],
            'password too short' => [
                'p4sS!',
                fn (AssertableJson $json) => $json
                    ->hasAll(['error', 'error.password', 'error.password.0'])
                    ->whereAllType(['error' => 'array', 'error.password' => 'array', 'error.password.0' => 'string'])
                    ->where('error.password.0', $message),
            ],
            'password no digit' => [
                'pAssssssssS!',
                fn (AssertableJson $json) => $json
                    ->hasAll(['error', 'error.password', 'error.password.0'])
                    ->whereAllType(['error' => 'array', 'error.password' => 'array', 'error.password.0' => 'string'])
                    ->where('error.password.0', $message),
            ],
            'password no symbol' => [
                'pAsssssss12sSD',
                fn (AssertableJson $json) => $json
                    ->hasAll(['error', 'error.password', 'error.password.0'])
                    ->whereAllType(['error' => 'array', 'error.password' => 'array', 'error.password.0' => 'string'])
                    ->where('error.password.0', $message),
            ],
            'password no mixed case' => [
                'p4sssssss12s@ad',
                fn (AssertableJson $json) => $json
                    ->hasAll(['error', 'error.password', 'error.password.0'])
                    ->whereAllType(['error' => 'array', 'error.password' => 'array', 'error.password.0' => 'string'])
                    ->where('error.password.0', $message),
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidUserPasswordProvider')]
    public function it_should_validate_user_password(string $password, Closure $response): void
    {
        $this
            ->postJson(self::AUTHENTICATE_USER_ENDPOINT, [
                'email' => fake()->freeEmail(),
                'password' => $password,
            ])
            ->assertBadRequest()
            ->assertJson($response);
    }

    #[Test]
    public function it_should_return_error_when_user_does_not_exist(): void
    {
        $this
            ->postJson(self::AUTHENTICATE_USER_ENDPOINT, [
                'email' => fake()->freeEmail(),
                'password' => '@p5sSw0rd!',
            ])
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Authentication failed. Please check your credentials.')
            );
    }

    #[Test]
    public function it_should_return_error_when_password_is_incorrect(): void
    {
        LaravelUserModel::factory()->createOne(['email' => new Email('ilovelaravel@gmail.com')]);

        $this
            ->postJson(self::AUTHENTICATE_USER_ENDPOINT, [
                'email' => 'ilovelaravel@gmail.com',
                'password' => '@WrongPassword123!',
            ])
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Authentication failed. Please check your credentials.')
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
    public function it_should_return_error_when_user_is_not_active(StatusInterface $status): void
    {
        LaravelUserModel::factory()->createOne([
            'email' => new Email('ilovelaravel@gmail.com'),
            'password' => new Password('@p5sSw0rd!'),
            'status' => $status,
        ]);

        $this
            ->postJson(self::AUTHENTICATE_USER_ENDPOINT, [
                'email' => 'ilovelaravel@gmail.com',
                'password' => '@p5sSw0rd!',
            ])
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Authentication failed. Please check your credentials.')
            );
    }

    #[Test]
    public function it_should_return_error_when_email_not_verified(): void
    {
        LaravelUserModel::factory()->createOne([
            'email' => new Email('ilovelaravel@gmail.com'),
            'password' => new Password('@p5sSw0rd!'),
            'status' => new Active,
            'email_verified_at' => null,
        ]);

        $this
            ->postJson(self::AUTHENTICATE_USER_ENDPOINT, [
                'email' => 'ilovelaravel@gmail.com',
                'password' => '@p5sSw0rd!',
            ])
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Authentication failed. Please check your credentials.')
            );
    }

    #[Test]
    public function it_should_return_token_when_user_is_active_and_verified(): void
    {
        LaravelUserModel::factory()->createOne([
            'email' => new Email('ilovelaravel@gmail.com'),
            'password' => new Password('@p5sSw0rd!'),
            'status' => new Active,
        ]);

        $this
            ->postJson(self::AUTHENTICATE_USER_ENDPOINT, [
                'email' => 'ilovelaravel@gmail.com',
                'password' => '@p5sSw0rd!',
            ])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->hasAll(['data', 'data.token', 'data.type', 'data.expires_in'])
                ->whereAllType([
                    'data' => 'array',
                    'data.token' => 'string',
                    'data.type' => 'string',
                    'data.expires_in' => 'integer',
                ])
                ->where('data.type', 'Bearer')
                ->where('data.expires_in', 3600)
                ->etc()
            );
    }
}
