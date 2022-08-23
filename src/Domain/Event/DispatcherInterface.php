<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

interface DispatcherInterface
{
    public function dispatch(AbstractDomainEvent $event): void;
}
