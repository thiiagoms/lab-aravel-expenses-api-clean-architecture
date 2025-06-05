<?php

declare(strict_types=1);

namespace Src\Domain\User\ValueObjects;

final readonly class Password
{
    private const int MIN_LENGTH = 8;

    private string $password;

    public function __construct(string $password, bool $hashed = true)
    {
        $this->validate($password);

        $hashed === true
            ? $this->password = $this->hash($password)
            : $this->password = $password;
    }

    public function getValue(): string
    {
        return $this->password;
    }

    public function verifyPasswordMatch(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    private function checkPasswordMinLength(string $password): bool
    {
        return strlen($password) > self::MIN_LENGTH;
    }

    private function checkPasswordPatternMatch(string $password): bool
    {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/';

        return preg_match($pattern, $password) === 1;
    }

    private function validate(string $password): void
    {
        $message = 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.';

        if (! $this->checkPasswordMinLength($password) || ! $this->checkPasswordPatternMatch($password)) {
            throw new \InvalidArgumentException($message);
        }
    }

    private function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
