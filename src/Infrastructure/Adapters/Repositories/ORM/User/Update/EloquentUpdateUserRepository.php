<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM\User\Update;

use Src\Domain\Repositories\User\Update\UpdateUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Infrastructure\Adapters\Mappers\User\UserEntityToUserModelMapper;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

class EloquentUpdateUserRepository extends BaseEloquentRepository implements UpdateUserRepositoryInterface
{
    /** @var LaravelUserModel */
    protected $model = LaravelUserModel::class;

    public function update(User $user): User
    {
        /** @var LaravelUserModel $userModel */
        $userModel = $this->model->find($user->id()?->getValue());

        if ($userModel === null) {
            throw new \InvalidArgumentException('User not found');
        }

        $userModel = UserEntityToUserModelMapper::map($user, $userModel);

        $userModel->save();
        $userModel->refresh();

        return UserModelToUserEntityMapper::map($userModel);
    }
}
