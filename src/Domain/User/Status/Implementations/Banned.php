<?php

declare(strict_types=1);

namespace Src\Domain\User\Status\Implementations;

use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\Status\Exceptions\InvalidStatusTransitionException;
use Src\Domain\User\Status\Interfaces\StatusInterface;

final class Banned implements StatusInterface
{
    public function awaitingActivation(User $user): void
    {
        throw new InvalidStatusTransitionException(
            from: Status::BANNED,
            to: Status::AWAITING_ACTIVATION,
            user: $user
        );
    }

    public function activate(User $user): void
    {
        throw new InvalidStatusTransitionException(
            from: Status::BANNED,
            to: Status::ACTIVE,
            user: $user
        );
    }

    public function suspend(User $user): void
    {
        throw new InvalidStatusTransitionException(
            from: Status::BANNED,
            to: Status::SUSPENDED,
            user: $user
        );
    }

    public function ban(User $user): void
    {
        throw new InvalidStatusTransitionException(
            from: Status::BANNED,
            to: Status::BANNED,
            user: $user
        );
    }

    public function getStatus(): Status
    {
        return Status::BANNED;
    }
}
