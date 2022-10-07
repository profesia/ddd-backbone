<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test;

use Profesia\DddBackbone\Domain\Event\AbstractBusinessEvent;

class NullB2BEvent extends AbstractBusinessEvent
{
    public function getPayload(): array
    {
        return [
            'occurredOn' => $this->getOccurredOn()->format('Y-m-d H:i:s'),
            'primaryId'  => $this->getPrimaryId(),
            'businessId' => $this->getBusinessId(),
        ];
    }
}
