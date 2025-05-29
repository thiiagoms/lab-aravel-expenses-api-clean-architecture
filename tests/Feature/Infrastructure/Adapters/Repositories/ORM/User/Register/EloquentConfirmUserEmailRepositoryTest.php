<?php

declare(strict_types=1);

namespace Feature\Infrastructure\Adapters\Repositories\ORM\User\Register;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Find\EloquentFindUserByIdRepository;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Register\EloquentConfirmUserEmailRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Tests\TestCase;

final class EloquentConfirmUserEmailRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private EloquentConfirmUserEmailRepository $repository;

    private EloquentFindUserByIdRepository $findUserByIdRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentConfirmUserEmailRepository;

        $this->findUserByIdRepository = new EloquentFindUserByIdRepository;
    }

    #[Test]
    public function it_should_return_false_when_user_does_nos_exists(): void
    {
        $user = new User(
            name: new Name('John Moe'),
            email: new Email('ilovelaravel02@gmail.com'),
            password: new Password('P4sSw0RdStr0ng!@#@_'),
            id: new Id(fake()->uuid()),
        );

        $result = $this->repository->confirm($user);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_should_set_current_date_time_as_email_verified_at_when_user_email_is_not_already_confirmed(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSw0RdStr0ng!@#@_'),
            id: new Id(fake()->uuid()),
            role: Role::USER,
            status: new AwaitingActivation,
            emailConfirmedAt: null
        );

        LaravelUserModel::factory()->createOne([
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email(),
            'password' => $user->password(),
            'role' => $user->role(),
            'status' => $user->status(),
            'email_verified_at' => null,
        ]);

        $result = $this->repository->confirm($user);

        $this->assertTrue($result);

        $result = $this->findUserByIdRepository->find($user->id());

        $this->assertEquals($user->id(), $result->id());
        $this->assertNotNull($result->emailConfirmedAt());
        $this->assertTrue($result->isEmailAlreadyConfirmed());
    }

    #[Test]
    public function it_should_keep_email_verified_at_when_user_email_is_already_confirmed(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSw0RdStr0ng!@#@_'),
            id: new Id(fake()->uuid()),
            role: Role::USER,
            status: new AwaitingActivation,
            emailConfirmedAt: new \DateTimeImmutable
        );

        LaravelUserModel::factory()->createOne([
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email(),
            'password' => $user->password(),
            'role' => $user->role(),
            'status' => $user->status(),
            'email_verified_at' => $user->emailConfirmedAt(),
        ]);

        $result = $this->repository->confirm($user);

        $this->assertTrue($result);

        $result = $this->findUserByIdRepository->find($user->id());

        $this->assertEquals($user->id(), $result->id());
        $this->assertEquals(
            $user->emailConfirmedAt()->format('Y-m-d H:i:s'),
            $result->emailConfirmedAt()->format('Y-m-d H:i:s')
        );
        $this->assertNotNull($result->emailConfirmedAt());
        $this->assertTrue($result->isEmailAlreadyConfirmed());
    }
}
