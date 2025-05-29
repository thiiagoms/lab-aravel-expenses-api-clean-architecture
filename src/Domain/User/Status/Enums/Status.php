<?php

declare(strict_types=1);

namespace Src\Domain\User\Status\Enums;

enum Status: string
{
    case AWAITING_ACTIVATION = 'awaiting_activation';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';

    public function isAwaitingActivation(): bool
    {
        return $this === self::AWAITING_ACTIVATION;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function isBanned(): bool
    {
        return $this === self::BANNED;
    }
}
