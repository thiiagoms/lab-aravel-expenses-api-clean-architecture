<?php

declare(strict_types=1);

namespace Src\Infrastructure\Support;

final readonly class ArrayCleaner
{
    public static function removeEmpty(array $payload): array
    {
        return array_filter($payload, fn (mixed $value): bool => ! empty($value));
    }
}
