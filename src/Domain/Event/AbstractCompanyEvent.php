<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractCompanyEvent extends AbstractDomainEvent
{
    public function __construct(
        string $primaryId,
        private string $companyId,
        ?DateTimeImmutable $occurredOn = null)
    {
        parent::__construct($primaryId, $occurredOn);
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }
}
