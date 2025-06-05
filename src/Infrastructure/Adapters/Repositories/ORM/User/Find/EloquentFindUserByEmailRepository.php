<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM\User\Find;

use Src\Domain\Repositories\User\Find\FindUserByEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\User\ValueObjects\Email;
use Src\Infrastructure\Adapters\Mappers\User\UserModelToUserEntityMapper;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

final class EloquentFindUserByEmailRepository extends BaseEloquentRepository implements FindUserByEmailRepositoryInterface
{
    /**
     * @var LaravelUserModel
     */
    protected $model = LaravelUserModel::class;

    public function find(Email $email): ?User
    {
        $user = $this->model->where('email', $email->getValue())->first();

        if ($user === null) {
            return null;
        }

        return UserModelToUserEntityMapper::map($user);
    }
}
