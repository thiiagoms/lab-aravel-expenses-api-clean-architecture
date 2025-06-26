<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Register;

use Exception;
use Src\Application\UseCases\User\Exceptions\EmailAlreadyExistsException;
use Src\Application\UseCases\User\Register\DTO\RegisterUserDTO;
use Src\Application\UseCases\User\Register\Services\RegisterUserService;
use Src\Domain\User\Entities\User;
use Src\Domain\User\Factory\UserFactory;

final readonly class RegisterUserAction
{
    public function __construct(private RegisterUserService $service) {}

    /**
     * @throws EmailAlreadyExistsException|Exception
     */
    public function handle(RegisterUserDTO $dto): User
    {
        $user = UserFactory::fromDTO($dto);

        return $this->service->register($user);
    }
}
