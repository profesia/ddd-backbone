<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\DddBackbone\Domain\Event\DispatcherInterface;

class NullDispatcher implements DispatcherInterface
{
    public function dispatch(AbstractDomainEvent $event): void
    {
    }
}
