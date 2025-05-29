<?php

namespace Tests\Unit\Domain\User\Role\Enums;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\User\Role\Enums\Role;

class RoleTest extends TestCase
{
    #[Test]
    public function is_admin_should_returns_true_for_admin(): void
    {
        $role = Role::ADMIN;

        $this->assertTrue($role->isAdmin());
        $this->assertFalse($role->isUser());
    }

    #[Test]
    public function is_user_should_returns_true_for_user(): void
    {
        $role = Role::USER;
        $this->assertTrue($role->isUser());
        $this->assertFalse($role->isAdmin());
    }

    public static function roleProvider(): array
    {
        return [
            [Role::ADMIN, 'admin'],
            [Role::USER, 'user'],
        ];
    }

    #[Test]
    #[DataProvider('roleProvider')]
    public function map_should_returns_correct_enum(Role $expectedRole, string $roleName): void
    {
        $role = Role::map($roleName);
        $this->assertSame($expectedRole, $role);
    }

    #[Test]
    public function map_should_throws_exception_for_invalid_role(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid role: 'manager'");

        Role::map('manager');
    }
}
