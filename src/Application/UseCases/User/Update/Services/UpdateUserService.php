<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Update\Services;

use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Application\UseCases\User\Shared\Validators\VerifyUserEmailIsAvailable;
use Src\Application\UseCases\User\Update\DTO\UpdateUserDTO;
use Src\Domain\Repositories\User\Update\UpdateUserRepositoryInterface;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

readonly class UpdateUserService
{
    public function __construct(
        private FindOrFailUserByIdService $findOrFailUserByIdService,
        private VerifyUserEmailIsAvailable $userEmailIsAvailable,
        private UpdateUserRepositoryInterface $repository,
    ) {}

    public function update(UpdateUserDTO $dto): User
    {
        $user = $this->loadExistingUser($dto->id());

        $this->ensureEmailIsAvailable($dto, $user);

        $user = UserEntityUpdater::update($user, $dto);

        return $this->repository->update($user);
    }

    private function loadExistingUser(Id $id): User
    {
        return $this->findOrFailUserByIdService->findOrFail($id);
    }

    /**
     * @throws EmailAlreadyExistsException
     */
    private function ensureEmailIsAvailable(UpdateUserDTO $dto, User $user): void
    {
        if ($this->shouldSkipEmailValidation($dto, $user)) {
            return;
        }

        $this->userEmailIsAvailable->verify($dto->email());
    }

    private function shouldSkipEmailValidation(UpdateUserDTO $dto, User $user): bool
    {
        return empty($dto->email()) || $dto->email()->equals($user->email());
    }
}
