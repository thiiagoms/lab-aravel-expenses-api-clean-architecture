<?php

declare(strict_types=1);

namespace Src\Application\UseCases\User\Profile;

use Src\Application\UseCases\User\Shared\Services\FindOrFailUserByIdService;
use Src\Domain\User\Entities\User;
use Src\Domain\ValueObjects\Id;

final readonly class ProfileAction
{
    public function __construct(private FindOrFailUserByIdService $service) {}

    public function handle(Id $id): User
    {
        return $this->service->findOrFail($id);
    }
}
