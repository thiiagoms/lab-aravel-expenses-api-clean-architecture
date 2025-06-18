<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

class ProfileApiRequest extends FormRequest
{
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
}
