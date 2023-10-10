<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Domain\Event\AbstractUserEvent;

class NullB2CEvent extends AbstractUserEvent
{
    public function getPayload(): array
    {
        return [
            'occurredOn' => $this->getOccurredOn()->format('Y-m-d H:i:s'),
            'primaryId'  => $this->getPrimaryId(),
            'userId' => $this->getUserId(),
        ];
    }

    public function getPublicName(): string
    {
        return 'tests/NullB2CEvent';
    }
}
