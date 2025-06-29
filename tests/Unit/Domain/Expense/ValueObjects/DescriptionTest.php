<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Expense\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\Expense\ValueObjects\Description;

class DescriptionTest extends TestCase
{
    public static function invalidDescriptionProvider(): array
    {
        return [
            'it should throws exception for empty description' => [
                '',
            ],
            'it should throws exception for short description' => [
                'ab',
            ],
            'it should throws exception for description with only spaces' => [
                '     ',
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidDescriptionProvider')]
    public function it_should_throws_exception_for_invalid_description(string $description): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Description cannot be empty and must be at least 3 characters long.');
        new Description($description);
    }

    public static function validDescriptionProvider(): array
    {
        return [
            'it should create description with valid value' => [
                'Valid description',
            ],
            'it should create description with minimum length' => [
                'abc',
            ],
            'it should create description with spaces' => [
                '   valid   ',
            ],
            'it should sanitize description with HTML tags' => [
                '   <b>Test</b>   ',
            ],
            'it should hanle multibyte characters' => [
                'áß😊',
            ],
            'it should accept a very long description' => [
                str_repeat('a', 1000),
            ],
        ];
    }

    #[Test]
    #[DataProvider('validDescriptionProvider')]
    public function it_should_create_description_with_valid_value(string $description): void
    {
        $descriptionObject = new Description($description);
        $this->assertSame(trim(strip_tags($description)), $descriptionObject->getValue());
    }

    #[Test]
    public function it_should_handle_newline_characters(): void
    {
        $description = new Description("line1\nline2");
        $this->assertStringContainsString('line1', $description->getValue());
        $this->assertStringContainsString('line2', $description->getValue());
    }
}
