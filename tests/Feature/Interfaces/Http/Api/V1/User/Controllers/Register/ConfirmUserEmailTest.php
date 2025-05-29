<?php

namespace Feature\Interfaces\Http\Api\V1\User\Controllers\Register;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class ConfirmUserEmailTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_should_return_invalid_signature_message_when_try_to_sign_invalid_email(): void
    {
        $this
            ->getJson('/api/v1/email-confirmation')
            ->assertBadRequest()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Invalid signature.')
            );
    }

    #[Test]
    public function it_should_confirm_user_email_with_signed_url(): void
    {
        $user = UserModelToUserEntityMapper::map(
            LaravelUserModel::factory()->createOne(['email_verified_at' => null, 'status' => new AwaitingActivation])
        );

        $url = URL::temporarySignedRoute(
            'api.v1.email-confirmation',
            now()->addMinutes(60),
            ['id' => $user->id()->getValue()]
        );

        $this
            ->getJson($url)
            ->assertOk()
            ->assertJson(fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', 'Email confirmed successfully.')
            );
    }
}
