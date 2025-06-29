<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Expense\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\Expense\ValueObjects\Amount;

class AmountTest extends TestCase
{
    public static function validAmountProvider(): array
    {
        return [
            'int value' => [100, 10000, 100.00],
            'float value' => [12.34, 1234, 12.34],
            'string value' => ['56.78', 5678, 56.78],
        ];
    }

    #[Test]
    #[DataProvider('validAmountProvider')]
    public function it_creates_amount_from_various_types(int|float|string $input, int $expectedCents, float $expectedDecimal): void
    {
        $amount = new Amount($input);

        $this->assertSame($expectedCents, $amount->getValue());
        $this->assertSame($expectedDecimal, $amount->getValueAsDecimal());
    }

    public static function invalidAmountProvider(): array
    {
        return [
            'zero string' => ['0', 0, 0.0],
            'zero int' => [0, 0, 0.0],
            'negative int' => [-1],
            'negative float' => [-12.34],
            'negative string' => ['-56.78'],
        ];
    }

    #[Test]
    #[DataProvider('invalidAmountProvider')]
    public function it_should_throws_exception_for_negative_amount(int|float|string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative or zero.');

        new Amount($input);
    }

    #[Test]
    public function it_should_compares_amount_equality(): void
    {
        $a = new Amount(10.00);
        $b = new Amount('10.00');
        $c = new Amount(15.00);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    #[Test]
    public function it_should_compares_amount_greater_than(): void
    {
        $a = new Amount(20.00);
        $b = new Amount(10.00);

        $this->assertTrue($a->greaterThan($b));
        $this->assertFalse($b->greaterThan($a));
    }

    #[Test]
    public function it_should_adds_amounts(): void
    {
        $a = new Amount(10.50);
        $b = new Amount(5.25);

        $sum = $a->add($b);

        $this->assertSame(1575, $sum->getValue());
        $this->assertSame(15.75, $sum->getValueAsDecimal());
    }

    #[Test]
    public function it_should_subtracts_amounts(): void
    {
        $a = new Amount(10.00);
        $b = new Amount(4.25);

        $diff = $a->subtract($b);

        $this->assertSame(575, $diff->getValue());
        $this->assertSame(5.75, $diff->getValueAsDecimal());
    }

    #[Test]
    public function it_should_throws_when_subtracting_to_negative(): void
    {
        $a = new Amount(5.00);
        $b = new Amount(10.00);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative or zero.');

        $a->subtract($b);
    }
}
