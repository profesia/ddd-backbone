<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application;

use Profesia\DddBackbone\Application\Event\DequeueDispatcherInterface;
use Profesia\DddBackbone\Domain\Exception\DomainException;

final class EventFlushingTransactionDecorator implements TransactionServiceInterface
{
    public function __construct(
        private TransactionServiceInterface $decoratedObject,
        private DequeueDispatcherInterface $dequeueTrigger
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
