<?php

declare(strict_types=1);

namespace Src\Domain\User\Role\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }

    public static function map(string $role): Role
    {
        return match ($role) {
            self::ADMIN->value => self::ADMIN,
            self::USER->value => self::USER,
            default => throw new \InvalidArgumentException("Invalid role: '{$role}'"),
        };
    }
}
