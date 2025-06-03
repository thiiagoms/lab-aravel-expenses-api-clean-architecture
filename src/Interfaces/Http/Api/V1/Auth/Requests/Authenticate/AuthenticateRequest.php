<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\Auth\Requests\Authenticate;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Src\Interfaces\Http\Api\V1\User\Rules\EmailIsValidRule;
use Src\Interfaces\Http\Api\V1\User\Rules\PasswordIsValidRule;

final class AuthenticateRequest extends FormRequest
{
    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST)
        );
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                new EmailIsValidRule,
            ],
            'password' => [
                new PasswordIsValidRule,
            ],
        ];
    }
}
