<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Shared\Services;

use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

readonly class FindOrFailUserByIdService
{
    public function __construct(private FindUserByIdRepositoryInterface $repository) {}

    /**
     * @throws UserNotFoundException
     */
    public function findOrFail(Id $id): User
    {
        $user = $this->repository->find($id);

        if ($user === null) {
            throw UserNotFoundException::create('User not found');
        }

        return $user;
    }
}
