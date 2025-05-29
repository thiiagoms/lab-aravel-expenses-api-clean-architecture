<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Repositories\ORM\User\Register;

use Src\Domain\Repositories\User\Register\ConfirmUserEmailRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Infrastructure\Adapters\Repositories\ORM\BaseEloquentRepository;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

final class EloquentConfirmUserEmailRepository extends BaseEloquentRepository implements ConfirmUserEmailRepositoryInterface
{
    /** @var LaravelUserModel */
    protected $model = LaravelUserModel::class;

    public function confirm(User $user): bool
    {
        $emailVerifiedAt = $user->isEmailAlreadyConfirmed()
            ? $user->emailConfirmedAt()
            : now()->toDateTimeImmutable();

        return (bool) $this->model->find($user->id()->getValue())?->update([
            'status' => $user->status(),
            'email_verified_at' => $emailVerifiedAt->format('Y-m-d H:i:s'),
        ]);
    }
}
