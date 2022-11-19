<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test;

use Profesia\DddBackbone\Domain\Event\AbstractUserEvent;

class NullB2CEvent extends AbstractUserEvent
{
    public function getPayload(): array
    {
        return [
            'occurredOn' => $this->getOccurredOn()->format('Y-m-d H:i:s'),
            'primaryId'  => $this->getPrimaryId(),
            'customerId' => $this->getUserId(),
        ];
    }
}
