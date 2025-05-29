<?php

declare(strict_types=1);

namespace Src\Domain\User\Status\Interfaces;

use Src\Domain\User\Entities\User;
use Src\Domain\User\Status\Enums\Status;

interface StatusInterface
{
    public function awaitingActivation(User $user): void;

    public function activate(User $user): void;

    public function suspend(User $user): void;

    public function ban(User $user): void;

    public function getStatus(): Status;
}
