<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Symfony\Event;

use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\DddBackbone\Domain\Event\DispatcherInterface;

class ExternalEventDispatcher implements DispatcherInterface
{
    public function __construct(
        private MetadataRegistry $registry,
        private DispatcherInterface $decoratedObject
    ) {
    }

    public function dispatch(AbstractDomainEvent $event): void
    {
        if ($this->registry->hasEventMetadata($event) === false) {
            return;
        }

        $this->decoratedObject->dispatch($event);
    }
}
