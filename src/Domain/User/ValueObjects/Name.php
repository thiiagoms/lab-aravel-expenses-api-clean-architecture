<?php

declare(strict_types=1);

namespace Src\Domain\User\ValueObjects;

use InvalidArgumentException;
use Src\Infrastructure\Support\Sanitizer;

final readonly class Name
{
    private const int MIN_LENGTH = 3;

    private const int MAX_LENGTH = 150;

    private string $name;

    public function __construct(string $name)
    {
        $name = Sanitizer::clean($name);

        $this->validate($name);

        $this->name = $this->transform($name);
    }

    public function getValue(): string
    {
        return $this->name;
    }

    private function validate(array|string $name): void
    {
        $this->validateLength($name);
        $this->validateOnlyLetters($name);
    }

    private function validateLength(string $name): void
    {
        if (strlen($name) < self::MIN_LENGTH || strlen($name) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Name must be between %d and %d characters.', self::MIN_LENGTH, self::MAX_LENGTH)
            );
        }
    }

    private function validateOnlyLetters(string $name): void
    {
        $pattern = preg_match('/^[a-zA-Z\s]+$/u', $name) === 1;

        if ($pattern === false) {
            throw new InvalidArgumentException('Name must contains only letters.');
        }
    }

    private function transform(string $name): string
    {
        return ucwords(mb_strtolower($name));
    }
}
