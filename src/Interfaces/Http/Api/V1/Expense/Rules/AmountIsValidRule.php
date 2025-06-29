<?php

namespace Src\Interfaces\Http\Api\V1\Expense\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Src\Domain\Expense\ValueObjects\Amount;

class AmountIsValidRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new Amount($value);
        } catch (\InvalidArgumentException $e) {
            $fail('Invalid amount provided. The expense amount must have a positive numeric value with up to two decimal places.');
        }
    }
}
