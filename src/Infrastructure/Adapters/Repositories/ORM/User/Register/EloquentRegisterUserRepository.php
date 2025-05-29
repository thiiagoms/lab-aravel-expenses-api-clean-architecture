<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM\User\Register;

use Src\Domain\Repositories\User\Register\RegisterUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

final class EloquentRegisterUserRepository extends BaseEloquentRepository implements RegisterUserRepositoryInterface
{
    /**
     * @var LaravelUserModel
     */
    protected $model = LaravelUserModel::class;

    public function save(User $user): User
    {
        $laravelUserModel = $this->model->create($user->toArray());

        return UserModelToUserEntityMapper::map($laravelUserModel);
    }
}
