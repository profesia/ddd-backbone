<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application;

use Profesia\DddBackbone\Application\Event\DequeueDispatcherInterface;
use Profesia\DddBackbone\Domain\Exception\AbstractDomainException;

final class EventFlushingTransactionDecorator implements TransactionServiceInterface
{
    private TransactionServiceInterface $decoratedObject;
    private DequeueDispatcherInterface $dequeueTrigger;

    public function __construct(
        TransactionServiceInterface $decoratedObject,
        DequeueDispatcherInterface $dequeueTrigger
    ) {
        $this->decoratedObject = $decoratedObject;
        $this->dequeueTrigger  = $dequeueTrigger;
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
     * @throws AbstractDomainException
     */
    public function transactional(callable $func)
    {
        $result = $this->decoratedObject->transactional($func);
        $this->dequeueTrigger->flush();

        return $result;
    }

}
