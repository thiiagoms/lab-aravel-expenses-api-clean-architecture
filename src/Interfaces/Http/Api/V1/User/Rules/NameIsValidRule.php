<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Src\Domain\User\ValueObjects\Name;

class NameIsValidRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new Name($value);
        } catch (\InvalidArgumentException|\TypeError $e) {
            $fail('Name must be between 3 and 150 characters and contains only letters.');
        }
    }
}
