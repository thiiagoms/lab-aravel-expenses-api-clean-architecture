<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Infrastructure\Support\ArrayCleaner;

final class ArrayCleanerTest extends TestCase
{
    public static function removeEmptyProvider(): array
    {
        return [
            'should remove empty values from array and return new array without empty values' => [
                [
                    'foo' => 'foo',
                    'bar' => 'bar',
                    'qux' => '',
                ],
                [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ],
            ],
            'should return entire array if input array is not empty' => [
                [
                    'foo' => 'foo',
                    'bar' => 'bar',
                    'qux' => 'qux',
                ],
                [
                    'foo' => 'foo',
                    'bar' => 'bar',
                    'qux' => 'qux',
                ],
            ],
            'should return empty array if input array is empty' => [
                [],
                [],
            ],
        ];
    }

    #[Test]
    #[DataProvider('removeEmptyProvider')]
    public function it_should_remove_empty_values_from_array(string|array $payload, string|array $result): void
    {
        $this->assertEquals($result, ArrayCleaner::removeEmpty($payload));
    }
}
