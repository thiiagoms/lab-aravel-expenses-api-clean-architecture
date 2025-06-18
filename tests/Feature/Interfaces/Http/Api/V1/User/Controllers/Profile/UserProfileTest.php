<?php

namespace Feature\Interfaces\Http\Api\V1\User\Controllers\Profile;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use DatabaseTransactions;

    private const string PROFILE_USER_ENDPOINT = '/api/v1/user/profile';

    #[Test]
    public function it_should_return_unauthenticated_message_when_user_that_it_is_not_authenticated_try_to_retrieve_his_data(): void
    {
        $this
            ->getJson(self::PROFILE_USER_ENDPOINT)
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
            ->getJson(self::PROFILE_USER_ENDPOINT)
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
            ->getJson(self::PROFILE_USER_ENDPOINT)
            ->assertUnauthorized()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('error')
                ->whereType('error', 'string')
                ->where('error', 'This action is unauthorized.')
            );
    }

    #[Test]
    public function it_should_return_user_data(): void
    {
        $user = LaravelUserModel::factory()->create();

        auth('api')->attempt(['email' => $user->email->getValue(), 'password' => '@p5sSw0rd!']);

        $this
            ->getJson(self::PROFILE_USER_ENDPOINT)
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->whereType('data', 'array')
                ->where('data.id', $user->id->getValue())
                ->where('data.name', $user->name->getValue())
                ->where('data.email', $user->email->getValue())
                ->where('data.created_at', $user->created_at->format('Y-m-d H:i:s'))
                ->where('data.updated_at', $user->updated_at->format('Y-m-d H:i:s'))
            );
    }
}
