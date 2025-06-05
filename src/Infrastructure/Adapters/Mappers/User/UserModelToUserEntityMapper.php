<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Mappers\User;

use Src\Domain\User\Entities\User;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

abstract class UserModelToUserEntityMapper
{
    public static function map(LaravelUserModel $model): User
    {
        return new User(
            name: $model->name,
            email: $model->email,
            password: $model->password,
            id: $model->id,
            role: $model->role,
            status: $model->status,
            emailConfirmedAt: $model->email_verified_at
                ? $model->email_verified_at->toDateTimeImmutable()
                : null,
            createdAt: $model->created_at->toDateTimeImmutable(),
            updatedAt: $model->updated_at->toDateTimeImmutable()
        );
    }
}
