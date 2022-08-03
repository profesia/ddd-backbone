<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

interface EventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void;
}
