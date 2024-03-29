<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;

class NullEvent extends AbstractDomainEvent
{
    public function getPayload(): array
    {
        return [
            'test' => true
        ];
    }
}
