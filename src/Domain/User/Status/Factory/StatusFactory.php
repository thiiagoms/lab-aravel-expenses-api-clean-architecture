<?php

declare(strict_types=1);

namespace Src\Domain\User\Status\Factory;

use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\Status\Implementations\AwaitingActivation;
use Src\Domain\User\Status\Implementations\Banned;
use src\Domain\User\Status\Implementations\Suspended;
use Src\Domain\User\Status\Interfaces\StatusInterface;

final class StatusFactory
{
    public static function build(Status $status): StatusInterface
    {
        return match ($status) {
            Status::AWAITING_ACTIVATION => new AwaitingActivation,
            Status::ACTIVE => new Active,
            Status::SUSPENDED => new Suspended,
            Status::BANNED => new Banned
        };
    }
}
