<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application;

use Throwable;
use Profesia\DddBackbone\Application\Event\DequeueDispatcherInterface;

final class EventFlushingTransactionDecorator implements TransactionServiceInterface
{
    private TransactionServiceInterface $decoratedObject;
    private DequeueDispatcherInterface  $dequeueTrigger;

    public function __construct(
        TransactionServiceInterface $decoratedObject,
        DequeueDispatcherInterface $dequeueTrigger
    )
    {
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
     * @throws Throwable
     */
    public function transactional(callable $func)
    {
        try {
            $result = $this->decoratedObject->transactional($func);
            $this->dequeueTrigger->flush();
            $this->dequeueTrigger->clear();

            return $result;
        } catch (Throwable $e) {
            $this->dequeueTrigger->clear();

            throw $e;
        }
    }

}
