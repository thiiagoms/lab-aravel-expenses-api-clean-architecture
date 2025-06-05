<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Src\Domain\User\ValueObjects\Email;

class EmailIsValidRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new Email($value);
        } catch (\InvalidArgumentException|\TypeError $e) {
            $fail('The provided email address is not valid. Please enter a valid email.');
        }
    }
}
