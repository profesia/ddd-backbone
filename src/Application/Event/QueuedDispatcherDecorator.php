<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Domain\Event\DomainEvent;
use Profesia\DddBackbone\Domain\Event\EventDispatcherInterface;

class QueuedDispatcherDecorator implements EventDispatcherInterface, DequeueEventInterface
{
    /** @var DomainEvent[] */
    private array $queuedEvents = [];

    public function __construct(
        private EventDispatcherInterface $decoratedObject
    ) {
    }

    public function dispatch(DomainEvent $event): void
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
