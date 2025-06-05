<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Role\Exceptions\InvalidRoleTransitionException;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\Status\Exceptions\InvalidStatusTransitionException;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

final class UserTest extends TestCase
{
    private Name $name;

    private Email $email;

    private Password $password;

    protected function setUp(): void
    {
        $this->name = new Name('John Doe');
        $this->email = new Email('ilovelaravel@gmail.com');
        $this->password = new Password('P4SSw0ord!@#dASD_');
    }

    #[Test]
    public function it_should_create_user_with_user_role_and_awaiting_activation_by_default(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $this->assertEquals($this->name->getValue(), $user->name()->getValue());
        $this->assertEquals($this->email->getValue(), $user->email()->getValue());
        $this->assertEquals($this->password->getValue(), $user->password()->getValue());
        $this->assertEquals(Role::USER, $user->role());
        $this->assertEquals(Status::AWAITING_ACTIVATION, $user->status()->getStatus());
        $this->assertNull($user->emailConfirmedAt());
        $this->assertFalse($user->isEmailAlreadyConfirmed());
    }

    #[Test]
    public function it_should_allow_user_to_become_admin_whin_admin_user(): void
    {
        $admin = new User(
            name: new Name('John Admin Data'),
            email: new Email('laraveladmin@gmail.com'),
            password: new Password('P4SSw0ord!@#dASDASDASW!@#ASD_'),
            role: Role::ADMIN,
            status: new Active
        );

        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->becomeAdmin($admin);

        $this->assertNotEquals(Role::USER, $user->role());
        $this->assertEquals(Role::ADMIN, $user->role());
    }

    #[Test]
    public function it_should_not_allow_user_tries_to_become_admin_with_non_admin_user(): void
    {
        $admin = new User(
            name: new Name('John Admin Data'),
            email: new Email('laraveladmin@gmail.com'),
            password: new Password('P4SSw0ord!@#dASDASDASW!@#ASD_'),
            status: new Active
        );

        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $this->expectException(InvalidRoleTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid role transition from '%s' to '%s' on user '%s'",
                Role::USER->value,
                Role::ADMIN->value,
                $user->email()->getValue()
            )
        );

        $user->becomeAdmin($admin);

        $this->assertNotEquals(Role::ADMIN, $user->role());
        $this->assertEquals(Role::USER, $user->role());
    }

    #[Test]
    public function it_should_not_allow_user_to_become_admin_when_another_user_is_admin_but_is_not_active(): void
    {
        $admin = new User(
            name: new Name('John Admin Data'),
            email: new Email('laraveladmin@gmail.com'),
            password: new Password('P4SSw0ord!@#dASDASDASW!@#ASD_'),
            role: Role::ADMIN
        );

        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $this->expectException(InvalidRoleTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid role transition from '%s' to '%s' on user '%s'",
                Role::USER->value,
                Role::ADMIN->value,
                $user->email()->getValue()
            )
        );

        $user->becomeAdmin($admin);

        $this->assertNotEquals(Role::ADMIN, $user->role());
        $this->assertEquals(Role::USER, $user->role());
    }

    #[Test]
    public function it_should_allow_admin_user_become_user(): void
    {
        $admin = new User(
            name: new Name('John Admin Data'),
            email: new Email('laraveladmin@gmail.com'),
            password: new Password('P4SSw0ord!@#dASDASDASW!@#ASD_'),
            role: Role::ADMIN,
            status: new Active
        );

        $updateUserDateTime = $admin->updatedAt();

        $admin->becomeUser();

        $this->assertEquals(Role::USER, $admin->role());
        $this->assertNotEquals(Role::ADMIN, $admin->role());
        $this->assertNotEquals(
            $updateUserDateTime->format('Y-m-d H:i:s.u'),
            $admin->updatedAt()->format('Y-m-d H:i:s.u')
        );
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_awaiting_activation_goes_awaiting_activation_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $currentUpdatedAtTimeStamp = $user->updatedAt();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'awaiting_activation',
                'awaiting_activation',
                $user->email()->getValue()
            )
        );

        $user->awaitingActivation();

        $this->assertEquals($currentUpdatedAtTimeStamp, $user->updatedAt());
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_awaiting_activation_goes_suspended_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $currentUpdatedAtTimeStamp = $user->updatedAt();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'awaiting_activation',
                'suspended',
                $user->email()->getValue()
            )
        );

        $user->suspend();
        $this->assertEquals($currentUpdatedAtTimeStamp, $user->updatedAt());
    }

    #[Test]
    public function it_should_allow_when_user_with_awaiting_activation_goes_active_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $currentUpdatedAtTimeStamp = $user->updatedAt();

        $user->activate();

        $this->assertEquals(Status::ACTIVE, $user->status()->getStatus());
        $this->assertNotEquals(Status::AWAITING_ACTIVATION, $user->status()->getStatus());

        $this->assertNotEquals($currentUpdatedAtTimeStamp, $user->updatedAt());
    }

    #[Test]
    public function it_should_allow_when_user_with_awaiting_activation_goes_banned_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $currentUpdatedAtTimeStamp = $user->updatedAt();

        $user->ban();

        $this->assertEquals(Status::BANNED, $user->status()->getStatus());
        $this->assertNotEquals(Status::AWAITING_ACTIVATION, $user->status()->getStatus());

        $this->assertNotEquals($currentUpdatedAtTimeStamp, $user->updatedAt());
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_active_status_goes_to_awaiting_activation_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $currentUpdatedAtTimeStamp = $user->updatedAt();

        $user->activate();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'active',
                'awaiting_activation',
                $user->email()->getValue()
            )
        );

        $user->awaitingActivation();

        $this->assertEquals($currentUpdatedAtTimeStamp, $user->updatedAt());
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_active_status_goes_to_active_status_again(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'active',
                'active',
                $user->email()->getValue()
            )
        );

        $user->activate();
    }

    #[Test]
    public function it_should_allow_user_with_awaiting_action_status_goes_to_active_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();

        $this->assertEquals(Status::ACTIVE, $user->status()->getStatus());
    }

    #[Test]
    public function it_should_allow_user_with_active_status_goes_to_suspend_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();

        $user->suspend();

        $this->assertEquals(Status::SUSPENDED, $user->status()->getStatus());
        $this->assertNotEquals(Status::ACTIVE, $user->status()->getStatus());
    }

    #[Test]
    public function it_should_allow_user_with_active_status_goes_to_ban_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();

        $user->ban();

        $this->assertEquals(Status::BANNED, $user->status()->getStatus());
        $this->assertNotEquals(Status::ACTIVE, $user->status()->getStatus());
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_suspended_status_goes_to_awaiting_activation_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();
        $user->suspend();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'suspended',
                'awaiting_activation',
                $user->email()->getValue()
            )
        );

        $user->awaitingActivation();
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_suspended_status_goes_to_suspended_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();
        $user->suspend();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'suspended',
                'suspended',
                $user->email()->getValue()
            )
        );

        $user->suspend();
    }

    #[Test]
    public function it_should_allow_when_user_with_suspended_status_goes_to_active_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();
        $user->suspend();

        $user->activate();

        $this->assertEquals(Status::ACTIVE, $user->status()->getStatus());
        $this->assertNotEquals(Status::SUSPENDED, $user->status()->getStatus());
    }

    #[Test]
    public function it_should_allow_when_user_with_suspended_status_goes_to_ban_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();
        $user->suspend();

        $user->ban();

        $this->assertEquals(Status::BANNED, $user->status()->getStatus());
        $this->assertNotEquals(Status::SUSPENDED, $user->status()->getStatus());
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_banned_status_goes_to_awaiting_activation_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->ban();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'banned',
                'awaiting_activation',
                $user->email()->getValue()
            )
        );

        $user->awaitingActivation();
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_banned_status_goes_to_active_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->ban();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'banned',
                'active',
                $user->email()->getValue()
            )
        );

        $user->activate();
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_banned_status_goes_to_banned_status_again(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->ban();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'banned',
                'banned',
                $user->email()->getValue()
            )
        );

        $user->ban();
    }

    #[Test]
    public function it_should_throw_exception_when_user_with_banned_status_goes_to_suspended_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->ban();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid status transition from '%s' to '%s' on user: '%s'.",
                'banned',
                'suspended',
                $user->email()->getValue()
            )
        );

        $user->suspend();
    }

    #[Test]
    public function it_should_allow_user_to_transaction_to_any_status(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->activate();

        $updateCurrentStatusTimeStamp = $user->updatedAt();

        $user->setStatus(new AwaitingActivation);

        $this->assertNotEquals(Status::ACTIVE, $user->status()->getStatus());
        $this->assertNotEquals(
            $updateCurrentStatusTimeStamp->format('Y-m-d H:i:s.u'),
            $user->updatedAt()->format('Y-m-d H:i:s.u')
        );
    }

    #[Test]
    public function it_should_allow_user_to_change_name(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $newName = new Name('Jane Doe');

        $user->changeName($newName);

        $this->assertEquals($newName->getValue(), $user->name()->getValue());
        $this->assertNotEquals($this->name->getValue(), $user->name()->getValue());
    }

    #[Test]
    public function it_should_allow_user_to_change_email(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $newEmail = new Email('phpdeveloper@gmail.com');

        $user->changeEmail($newEmail);

        $this->assertEquals($newEmail->getValue(), $user->email()->getValue());
        $this->assertNotEquals($this->email->getValue(), $user->email()->getValue());
    }

    #[Test]
    public function it_should_allow_user_to_change_password(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $newPassword = new Password('N3wP4SSw0rd!@#');

        $user->changePassword($newPassword);

        $this->assertEquals($newPassword->getValue(), $user->password()->getValue());
        $this->assertNotEquals($this->password->getValue(), $user->password()->getValue());
        $this->assertTrue($user->password()->verifyPasswordMatch('N3wP4SSw0rd!@#'));
        $this->assertFalse($user->password()->verifyPasswordMatch('P4SSw0ord!@#dASD_'));
    }

    #[Test]
    public function it_should_mark_user_email_as_confirmed(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password
        );

        $user->markEmailAsConfirmed();

        $this->assertNotNull($user->emailConfirmedAt());
        $this->assertTrue($user->isEmailAlreadyConfirmed());
    }

    #[Test]
    public function it_should_convert_user_entity_into_array(): void
    {
        $user = new User(
            name: $this->name,
            email: $this->email,
            password: $this->password,
            id: new Id(fake()->uuid())
        );

        $id = $user->id();

        $createdAt = $user->createdAt();
        $updatedAt = $user->updatedAt();

        $userArray = $user->toArray();

        $this->assertIsArray($userArray);

        $this->assertArrayHasKey('id', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
        $this->assertArrayHasKey('password', $userArray);
        $this->assertArrayHasKey('role', $userArray);
        $this->assertArrayHasKey('status', $userArray);
        $this->assertArrayHasKey('emailConfirmedAt', $userArray);
        $this->assertArrayHasKey('createdAt', $userArray);
        $this->assertArrayHasKey('updatedAt', $userArray);

        $this->assertEquals($id, $userArray['id']);
        $this->assertEquals($this->name, $userArray['name']);
        $this->assertEquals($this->email, $userArray['email']);
        $this->assertEquals($this->password, $userArray['password']);
        $this->assertEquals(Role::USER, $userArray['role']);
        $this->assertEquals(new AwaitingActivation, $userArray['status']);
        $this->assertNull($userArray['emailConfirmedAt']);
        $this->assertEquals($createdAt->format('Y-m-d H:i:s.u'), $userArray['createdAt']->format('Y-m-d H:i:s.u'));
        $this->assertEquals($updatedAt->format('Y-m-d H:i:s.u'), $userArray['updatedAt']->format('Y-m-d H:i:s.u'));
    }
}
