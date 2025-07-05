<?php

declare(strict_types=1);

namespace Src\Domain\Expense\ValueObjects;

use InvalidArgumentException;

final readonly class Amount
{
    private int $cents;

    private const int CENT_MULTIPLIER = 100;

    public function __construct(float|int|string $value)
    {
        $this->validate($value);

        $value = (float) $value;

        $this->cents = intval(round($value * self::CENT_MULTIPLIER));
    }

    public function getValue(): int
    {
        return $this->cents;
    }

    public function getValueAsDecimal(): float
    {
        return $this->cents / self::CENT_MULTIPLIER;
    }

    public function equals(Amount $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function greaterThan(Amount $other): bool
    {
        return $this->cents > $other->cents;
    }

    public function add(Amount $other): self
    {
        return new self(($this->cents + $other->cents) / self::CENT_MULTIPLIER);
    }

    public function subtract(Amount $other): self
    {
        if ($this->cents < $other->cents) {
            throw new InvalidArgumentException('Amount cannot be negative or zero.');
        }

        return new self(($this->cents - $other->cents) / self::CENT_MULTIPLIER);
    }

    private function validate(float|int|string $value): void
    {
        if ($value <= 0 || ! is_numeric($value)) {
            throw new InvalidArgumentException('Amount cannot be negative or zero.');
        }
    }
}
