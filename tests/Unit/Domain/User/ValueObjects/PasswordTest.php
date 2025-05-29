<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\User\ValueObjects\Password;

class PasswordTest extends TestCase
{
    public static function invalidPasswordProvider(): array
    {
        $message = 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.';

        return [
            'it should throw exception when provided password is empty' => [
                '',
                $message,
            ],
            'it should throw exception when provided password length is less than 8' => [
                'short1!',
                $message,
            ],
            'it should throw exception when provided password does not contain at least one uppercase letter' => [
                'lowercase1!',
                $message,
            ],
            'it should throw exception when provided password does not contain at least one lowercase letter' => [
                'UPPERCASE1!',
                $message,
            ],
            'it should throw exception when provided password does not contain at least one digit' => [
                'Uppercase!',
                $message,
            ],
            'it should throw exception when provided password does not contain at least one special character' => [
                'Uppercase1',
                $message,
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidPasswordProvider')]
    public function it_should_throw_exception_when_provided_password_is_invalid(string $password, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        new Password($password);
    }

    #[Test]
    public function it_should_create_password_when_provided_password_is_valid(): void
    {
        $password = new Password('ValidPassword1!');

        $this->assertInstanceOf(Password::class, $password);
        $this->assertIsString($password->getValue());
        $this->assertNotEquals('ValidPassword1!', $password->getValue());
        $this->assertTrue($password->verifyPasswordMatch('ValidPassword1!'));
    }

    #[Test]
    public function it_should_create_password_when_provided_password_is_valid_and_not_hashed(): void
    {
        $password = new Password('ValidPassword1!', false);

        $this->assertInstanceOf(Password::class, $password);
        $this->assertSame('ValidPassword1!', $password->getValue());
        $this->assertFalse($password->verifyPasswordMatch('ValidPassword1!'));
    }

    #[Test]
    public function it_should_return_false_when_password_does_not_match(): void
    {
        $password = new Password('ValidPassword1!');

        $this->assertFalse($password->verifyPasswordMatch('InvalidPassword1!'));
    }
}
