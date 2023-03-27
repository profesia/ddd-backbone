<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application;

interface TransactionServiceInterface
{
    public function start(): void;

    public function commit(): void;

    public function rollback(): void;

    public function transactional(callable $func);
}

