<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration;

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

    public function transactional(callable $func): mixed
    {
        return $func();
    }

}
