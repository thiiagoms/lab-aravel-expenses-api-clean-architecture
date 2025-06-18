<?php

namespace Tests\Feature\Infrastructure\Adapters\Repositories\ORM\User\Update;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Update\EloquentUpdateUserRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

class EloquentUpdateUserRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private EloquentUpdateUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentUpdateUserRepository;
    }

    public static function invalidUserIdProvider(): array
    {
        return [
            [null],
            [new Id(fake()->uuid())],
        ];
    }

    #[Test]
    #[DataProvider('invalidUserIdProvider')]
    public function it_should_throw_invalid_argument_exception_when_user_id_does_not_exists(?Id $id): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSw0RdStr0ng!@#@_'),
            id: $id,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->repository->update($user);
    }

    #[Test]
    public function it_should_update_user_entity_and_return_updated_user_data(): void
    {
        $userModel = LaravelUserModel::factory()->createOne();

        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSw0RdStr0ng!@#@_'),
            id: $userModel->id,
        );

        $result = $this->repository->update($user);

        $this->assertEquals($user->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($user->email()->getValue(), $result->email()->getValue());
        $this->assertTrue($result->password()->verifyPasswordMatch('P4sSw0RdStr0ng!@#@_'));
    }
}
