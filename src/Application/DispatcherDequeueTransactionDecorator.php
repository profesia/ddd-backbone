<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application;

use Profesia\DddBackbone\Domain\Event\DequeueEventInterface;
use Profesia\DddBackbone\Domain\Exception\DomainException;

class DispatcherDequeueTransactionDecorator implements TransactionServiceInterface
{
    public function __construct(
        private TransactionServiceInterface $decoratedObject,
        private DequeueEventInterface $dequeueTrigger
    ) {
    }

    public function start(): void
    {
        $this->decoratedObject->start();
    }

    public function commit(): void
    {
        $this->decoratedObject->commit();
    }

    public function rollback(): void
    {
        $this->decoratedObject->rollback();
    }

    /**
     * @param callable $func
     *
     * @return mixed
     * @throws DomainException
     */
    public function transactional(callable $func): mixed
    {
        $result = $this->decoratedObject->transactional($func);
        $this->dequeueTrigger->flush();

        return $result;
    }

}
