<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;

final class NullUnregisteredEvent extends AbstractDomainEvent
{
    public function getPayload(): array
    {
        return [];
    }
}