<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Requests\Register;

use Illuminate\Foundation\Http\FormRequest;
use Src\Interfaces\Http\Api\v1\User\Rules\IdIsValidRule;

final class ConfirmEmailApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => new IdIsValidRule,
            'expires' => ['required', 'integer'],
            'signature' => ['required', 'string'],
        ];
    }
}
