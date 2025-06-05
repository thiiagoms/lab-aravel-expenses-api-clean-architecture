<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\User\ValueObjects\Name;

class NameTest extends TestCase
{
    public static function invalidNameProvider(): array
    {
        return [
            'should throw exception when provided name is empty' => [
                '',
                'Name must be between 3 and 150 characters.',
            ],
            'should throw exception when provided name length is less than 3' => [
                'ab',
                'Name must be between 3 and 150 characters.',
            ],
            'should throw exception when provided name length is greater than 150' => [
                str_repeat('a', 160),
                'Name must be between 3 and 150 characters.',
            ],
            'should throw exception when provided name is numeric' => [
                '123456',
                'Name must contains only letters.',
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidNameProvider')]
    public function it_should_validate_name_when_provided_name_is_invalid(string $name, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new Name($name);
    }

    #[Test]
    public function it_should_transform_each_first_letter_name_to_upper_case(): void
    {
        $name = new Name('john doe');
        $this->assertEquals('John Doe', $name->getValue());

        $name = new Name('jane smith');
        $this->assertEquals('Jane Smith', $name->getValue());

        $name = new Name('JOHN DOE');
        $this->assertEquals('John Doe', $name->getValue());
    }
}
