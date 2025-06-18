<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Find;

use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Src\Domain\Repositories\User\Find\FindUserByIdRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

final readonly class FindUserByIdAction implements Interface\FindUserByIdActionInterface
{
    public function __construct(private FindUserByIdRepositoryInterface $repository) {}

    /**
     * Handle the action to find a user by their ID.
     *
     * @param  Id  $id  The ID of the user to find.
     * @return User The found user entity.
     *
     * @throws UserNotFoundException If the user is not found.
     */
    public function handle(Id $id): User
    {
        $user = $this->repository->find($id);

        if ($user === null) {
            throw UserNotFoundException::create('User not found');
        }

        return $user;
    }
}
