<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObjects;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\User\ValueObjects\Email;

final class EmailTest extends TestCase
{
    public static function invalidEmailProvider(): array
    {
        return [
            'should throw exception when provided email is not a valid email address' => [
                'invalid-email',
            ],
            'should throw exception when provided email is a empty string' => [
                '',
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function it_should_throw_exception_when_provided_email_value_is_invalid(string $email): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid e-mail address given: '{$email}'");

        new Email($email);
    }

    #[Test]
    public function it_should_create_email_when_provided_email_is_valid(): void
    {
        $email = new Email('ilovelaravel@gmail.com');

        $this->assertSame('ilovelaravel@gmail.com', $email->getValue());
    }

    public static function emailProvided(): array
    {
        return [
            [new Email('ilovelaravel@gmail.com'), true],
            [new Email('ilovephp@gmail.com'), false],
        ];
    }

    #[Test]
    #[DataProvider('emailProvided')]
    public function it_should_validate_email_matches(Email $email, bool $emailMatch): void
    {
        $this->assertSame($emailMatch, (new Email('ilovelaravel@gmail.com'))->equals($email));
    }
}
