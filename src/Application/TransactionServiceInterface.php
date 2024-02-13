<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application;

use Throwable;

interface TransactionServiceInterface
{
    public function start(): void;

    public function commit(): void;

    public function rollback(): void;

    /**
     * @param callable $func
     * @return mixed
     */
    public function transactional(callable $func);
}

