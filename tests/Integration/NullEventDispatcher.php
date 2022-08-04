<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test\Integration;


use Profesia\DddBackbone\Domain\Event\DomainEvent;
use Profesia\DddBackbone\Domain\Event\EventDispatcherInterface;

class NullEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void
    {
    }
}
