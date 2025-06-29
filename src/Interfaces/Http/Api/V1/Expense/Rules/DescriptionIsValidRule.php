<?php

namespace Src\Interfaces\Http\Api\V1\Expense\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Src\Domain\Expense\ValueObjects\Description;

class DescriptionIsValidRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new Description($value);
        } catch (\InvalidArgumentException $e) {
            $fail($e->getMessage());
        }
    }
}
