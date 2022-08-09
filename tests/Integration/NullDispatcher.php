<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test\Integration;


use Profesia\DddBackbone\Domain\Event\DomainEvent;
use Profesia\DddBackbone\Domain\Event\DispatcherInterface;

class NullDispatcher implements DispatcherInterface
{
    public function dispatch(DomainEvent $event): void
    {
    }
}
