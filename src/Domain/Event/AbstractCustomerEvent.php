<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractCustomerEvent extends AbstractDomainEvent
{
    public function __construct(
        string $primaryId,
        private string $customerId,
        ?DateTimeImmutable $occurredOn = null)
    {
        parent::__construct($primaryId, $occurredOn);
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
