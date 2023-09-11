<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;

class NullEventWithOverriddenName extends AbstractDomainEvent
{
    public function getPayload(): array
    {
        // TODO: Implement getPayload() method.
    }

    public static function getEventName(): string
    {
        return 'Testing';
    }
}