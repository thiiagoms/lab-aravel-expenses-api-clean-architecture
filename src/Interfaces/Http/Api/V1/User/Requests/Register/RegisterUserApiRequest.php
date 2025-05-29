<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Requests\Register;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Src\Interfaces\Http\Api\V1\User\Rules\EmailIsValidRule;
use Src\Interfaces\Http\Api\V1\User\Rules\NameIsValidRule;
use Src\Interfaces\Http\Api\V1\User\Rules\PasswordIsValidRule;
use Symfony\Component\HttpFoundation\Response;

final class RegisterUserApiRequest extends FormRequest
{
    /**
     * @throws HttpResponseException
     */
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
            'name' => [
                new NameIsValidRule,
            ],
            'email' => [
                'unique:users,email',
                new EmailIsValidRule,
            ],
            'password' => [
                new PasswordIsValidRule,
            ],
        ];
    }
}
