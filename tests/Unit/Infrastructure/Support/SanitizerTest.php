<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Infrastructure\Support\Sanitizer;

final class SanitizerTest extends TestCase
{
    public static function sanitizerCleanProvider(): array
    {
        return [
            'should remove spaces and html tags from string' => [
                ' <h1>Hello World</h1> ',
                'Hello World',
            ],
            'should remove spaces and html tags from each element of array' => [
                [
                    ' Hello World ',
                    ' <script>console.log("Hello World")</script> ',
                ],
                [
                    'Hello World',
                    'console.log("Hello World")',
                ],
            ],
            'should return empty array if input is empty' => [
                [],
                [],
            ],
        ];
    }

    #[Test]
    #[DataProvider('sanitizerCleanProvider')]
    public function it_should_sanitizer_data(string|array $payload, string|array $result): void
    {
        $this->assertEquals($result, Sanitizer::clean($payload));
    }
}
