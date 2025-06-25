<?php

declare(strict_types=1);

namespace Src\Infrastructure\Adapters\Transaction;

use Illuminate\Support\Facades\DB;
use Src\Domain\Repositories\Transaction\TransactionManagerInterface;

final class LaravelTransactionManager implements TransactionManagerInterface
{
    public function makeTransaction(\Closure $callback): mixed
    {
        try {
            $this->begin();

            $result = $callback();

            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    private function begin(): void
    {
        DB::beginTransaction();
    }

    private function commit(): void
    {
        DB::commit();
    }

    private function rollback(): void
    {
        DB::rollBack();
    }
}
