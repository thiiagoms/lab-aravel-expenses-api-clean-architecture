<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Src\Domain\User\ValueObjects\Password;

class PasswordIsValidRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new Password($value);
        } catch (\InvalidArgumentException|\TypeError $e) {
            $fail('Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.');
        }
    }
}
