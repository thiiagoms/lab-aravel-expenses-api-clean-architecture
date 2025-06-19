<?php

namespace Src\Interfaces\Http\Api\V1\User\Requests\Update;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Src\Interfaces\Http\Api\V1\User\Rules\EmailIsValidRule;
use Src\Interfaces\Http\Api\V1\User\Rules\NameIsValidRule;
use Src\Interfaces\Http\Api\V1\User\Rules\PasswordIsValidRule;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserApiRequest extends FormRequest
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
        if (! auth('api')->check()) {
            return false;
        }

        /** @var LaravelUserModel $userModel */
        $userModel = auth('api')->user();

        if ($userModel === null) {
            return false;
        }

        $user = UserModelToUserEntityMapper::map($userModel);

        return $user->status()->getStatus()->isActive() && $user->isEmailAlreadyConfirmed();
    }

    private function patch(): array
    {
        return [
            'name' => [
                'sometimes',
                new NameIsValidRule,
            ],
            'email' => [
                'sometimes',
                new EmailIsValidRule,
            ],
            'password' => [
                'sometimes',
                new PasswordIsValidRule,
            ],
        ];
    }

    private function put(): array
    {
        return [
            'name' => [
                'required',
                new NameIsValidRule,
            ],
            'email' => [
                'required',
                new EmailIsValidRule,
            ],
            'password' => [
                'required',
                new PasswordIsValidRule,
            ],
        ];
    }

    public function rules(): array
    {
        return match ($this->method()) {
            'PATCH' => $this->patch(),
            default => $this->put()
        };
    }
}
