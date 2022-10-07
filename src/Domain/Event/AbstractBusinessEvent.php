<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractBusinessEvent extends AbstractDomainEvent
{
    public function __construct(
        string $primaryId,
        private string $businessId,
        ?DateTimeImmutable $occurredOn = null)
    {
        parent::__construct($primaryId, $occurredOn);
    }

    public function getBusinessId(): string
    {
        return $this->businessId;
    }
}
