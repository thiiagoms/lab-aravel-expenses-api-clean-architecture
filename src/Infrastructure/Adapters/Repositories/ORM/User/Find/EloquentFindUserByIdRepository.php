<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM\User\Find;

use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

final class EloquentFindUserByIdRepository extends BaseEloquentRepository implements FindUserByIdRepositoryInterface
{
    /**
     * @var LaravelUserModel
     */
    protected $model = LaravelUserModel::class;

    public function find(Id $id): ?User
    {
        $user = $this->model->find($id->getValue());

        return $user === null ? null : UserModelToUserEntityMapper::map($user);
    }
}
