<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Mappers\User;

use Carbon\Carbon;
use Src\Domain\User\Entities\User;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

abstract class UserEntityToUserModelMapper
{
    public static function map(User $user, ?LaravelUserModel $model = null): LaravelUserModel
    {
        $model ??= new LaravelUserModel;

        $model->id = $user->id();
        $model->name = $user->name();
        $model->email = $user->email();
        $model->password = $user->password();
        $model->created_at = Carbon::createFromImmutable($user->createdAt());
        $model->updated_at = Carbon::createFromImmutable($user->updatedAt());
        $model->email_verified_at = $user->emailConfirmedAt() === null ? null : Carbon::createFromImmutable($user->emailConfirmedAt());

        return $model;
    }
}
