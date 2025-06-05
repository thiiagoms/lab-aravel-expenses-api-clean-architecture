<?php

declare(strict_types=1);

namespace Src\Domain\Repositories\Transaction;

use Exception;

interface TransactionManagerInterface
{
    /**
     * @throws Exception
     */
    public function makeTransaction(\Closure $callback): mixed;
}
