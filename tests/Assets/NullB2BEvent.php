<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Domain\Event\AbstractCompanyEvent;

class NullB2BEvent extends AbstractCompanyEvent
{
    public function getPayload(): array
    {
        return [
            'occurredOn' => $this->getOccurredOn()->format('Y-m-d H:i:s'),
            'primaryId'  => $this->getPrimaryId(),
            'companyId' => $this->getCompanyId(),
        ];
    }
}
