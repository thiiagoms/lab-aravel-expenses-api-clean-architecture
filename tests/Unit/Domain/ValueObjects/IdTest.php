<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\ValueObjects\Id;

class IdTest extends TestCase
{
    public static function invalidIdProvider(): array
    {
        return [
            'it should throw exception when provided id is not a valid UUID' => [
                'invalid-id',
            ],
            'it should throw exception when provided id is an empty string' => [
                '',
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidIdProvider')]
    public function it_should_throw_exception_when_provided_id_is_invalid(string $id): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid id given: '{$id}'");

        new Id($id);
    }

    #[Test]
    public function it_should_create_id_when_provided_id_is_valid(): void
    {
        $id = new Id('550e8400-e29b-41d4-a716-446655440000');

        $this->assertInstanceOf(Id::class, $id);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $id->getValue());
    }
}
