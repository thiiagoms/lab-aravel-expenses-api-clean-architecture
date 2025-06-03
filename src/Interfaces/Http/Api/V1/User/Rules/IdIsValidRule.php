<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Src\Domain\ValueObjects\Id;

class IdIsValidRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new Id($value);
        } catch (\InvalidArgumentException|\TypeError $e) {
            $fail('The provided id is not valid.');
        }
    }
}
