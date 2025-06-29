<?php

declare(strict_types=1);

namespace Src\Application\Interfaces\Events;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
