<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\Event\DequeueDispatcherInterface;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;

class NullDequeueDispatcher implements DequeueDispatcherInterface
{
    public function flush(): void
    {
    }

    public function clear(): void
    {
    }

    public function dispatch(AbstractDomainEvent $event): void
    {
    }
}
