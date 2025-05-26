<?php

declare(strict_types=1);

namespace Src\Infrastructure\Support;

final readonly class Sanitizer
{
    public static function clean(string|array $params): array|string
    {
        return is_array($params)
            ? array_map(fn (mixed $field): string => trim(strip_tags($field)), $params)
            : trim(strip_tags($params));
    }
}
