<?php

declare(strict_types=1);

namespace Src\Domain\User\Status\Implementations;

use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\Status\Exceptions\InvalidStatusTransitionException;
use Src\Domain\User\Status\Interfaces\StatusInterface;

final class AwaitingActivation implements StatusInterface
{
    public function awaitingActivation(User $user): void
    {
        throw new InvalidStatusTransitionException(
            from: Status::AWAITING_ACTIVATION,
            to: Status::AWAITING_ACTIVATION,
            user: $user
        );
    }

    public function activate(User $user): void
    {
        $user->setStatus(new Active);
    }

    public function suspend(User $user): void
    {
        throw new InvalidStatusTransitionException(
            from: Status::AWAITING_ACTIVATION,
            to: Status::SUSPENDED,
            user: $user
        );
    }

    public function ban(User $user): void
    {
        $user->setStatus(new Banned);
    }

    public function getStatus(): Status
    {
        return Status::AWAITING_ACTIVATION;
    }
}
