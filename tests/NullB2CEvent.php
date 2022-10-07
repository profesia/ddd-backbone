<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test;

use Profesia\DddBackbone\Domain\Event\AbstractCustomerEvent;

class NullB2CEvent extends AbstractCustomerEvent
{
    public function getPayload(): array
    {
        return [
            'occurredOn' => $this->getOccurredOn()->format('Y-m-d H:i:s'),
            'primaryId'  => $this->getPrimaryId(),
            'customerId' => $this->getCustomerId(),
        ];
    }
}
