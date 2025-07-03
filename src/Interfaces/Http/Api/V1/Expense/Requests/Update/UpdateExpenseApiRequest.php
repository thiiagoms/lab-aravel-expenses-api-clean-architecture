<?php

namespace Src\Interfaces\Http\Api\V1\Expense\Requests\Update;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;
use Src\Interfaces\Http\Api\V1\Expense\Rules\AmountIsValidRule;
use Src\Interfaces\Http\Api\V1\Expense\Rules\DescriptionIsValidRule;
use Symfony\Component\HttpFoundation\Response;

class UpdateExpenseApiRequest extends FormRequest
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

    public function put(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                new AmountIsValidRule,
            ],
            'description' => [
                'required',
                new DescriptionIsValidRule,
            ],
        ];
    }

    public function patch(): array
    {
        return [
            'amount' => [
                'sometimes',
                'numeric',
                new AmountIsValidRule,
            ],
            'description' => [
                'sometimes',
                new DescriptionIsValidRule,
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

    public function messages(): array
    {
        return [
            'amount.required' => 'Invalid amount provided. The expense amount must have a positive numeric value with up to two decimal places.',
            'amount.numeric' => 'Invalid amount provided. The expense amount must have a positive numeric value with up to two decimal places.',
            'description.required' => 'Description cannot be empty and must be at least 3 characters long.',
        ];
    }
}
