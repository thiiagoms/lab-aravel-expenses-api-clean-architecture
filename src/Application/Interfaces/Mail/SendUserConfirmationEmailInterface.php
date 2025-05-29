<?php

declare(strict_types=1);

namespace Src\Application\Interfaces\Mail;

use Src\Domain\User\Entities\User;

interface SendUserConfirmationEmailInterface
{
    public function send(User $user): void;
}
