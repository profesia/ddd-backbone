<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\TransactionServiceInterface;

class NullTransactionService implements TransactionServiceInterface
{
    public function start(): void
    {
    }

    public function commit(): void
    {
    }

    public function rollback(): void
    {
    }

    /**
     * @param callable $func
     *
     * @return mixed
     */
    public function transactional(callable $func)
    {
        return $func();
    }

}
