<?php

declare(strict_types=1);

namespace Src\Domain\Expense\ValueObjects;

use InvalidArgumentException;
use Src\Infrastructure\Support\Sanitizer;

final readonly class Description
{
    private const int MIN_LENGTH = 3;

    private string $description;

    public function __construct(string $description)
    {
        $description = Sanitizer::clean($description);

        $this->validate($description);

        $this->description = $description;
    }

    public function getValue(): string
    {
        return $this->description;
    }

    private function validate(string $description): void
    {
        if (empty($description) || strlen($description) < self::MIN_LENGTH) {
            $message = 'Description cannot be empty and must be at least '.self::MIN_LENGTH.' characters long.';
            throw new InvalidArgumentException($message);
        }
    }
}
