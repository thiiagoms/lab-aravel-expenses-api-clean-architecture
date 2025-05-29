<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Adapters\Repositories\ORM\User\Register;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Infrastructure\Adapters\Repositories\ORM\User\Register\EloquentRegisterUserRepository;
use Tests\TestCase;

final class EloquentRegisterUserRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private EloquentRegisterUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentRegisterUserRepository;
    }

    #[Test]
    public function it_should_create_new_user_and_return_created_user_entity(): void
    {
        $user = new User(
            name: new Name('John Doe'),
            email: new Email('ilovelaravel@gmail.com'),
            password: new Password('P4sSw0RdStr0ng!@#@_'),
        );

        $result = $this->repository->save($user);

        $this->assertEquals($user->name()->getValue(), $result->name()->getValue());
        $this->assertEquals($user->email()->getValue(), $result->email()->getValue());
        $this->assertEquals(Role::USER, $result->role());
        $this->assertEquals(Status::AWAITING_ACTIVATION, $result->status()->getStatus());
        $this->assertTrue($result->password()->verifyPasswordMatch('P4sSw0RdStr0ng!@#@_'));
    }
}
