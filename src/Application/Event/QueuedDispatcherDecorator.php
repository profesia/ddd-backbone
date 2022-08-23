<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\DddBackbone\Domain\Event\DispatcherInterface;

final class QueuedDispatcherDecorator implements DispatcherInterface, DequeueDispatcherInterface
{
    /** @var AbstractDomainEvent[] */
    private array $queuedEvents = [];

    public function __construct(
        private DispatcherInterface $decoratedObject
    ) {
    }

    public function dispatch(AbstractDomainEvent $event): void
    {
        $this->queuedEvents[] = $event;
    }

    public function flush(): void
    {
        foreach ($this->queuedEvents as $event) {
            $this->decoratedObject->dispatch($event);
        }

        $this->queuedEvents = [];
    }
}
