<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractCompanyEvent extends AbstractDomainEvent
{
    private string $companyId;

    public function __construct(
        string $primaryId,
        string $companyId,
        ?DateTimeImmutable $occurredOn = null
    ) {
        $this->companyId = $companyId;
        parent::__construct($primaryId, $occurredOn);
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }
}
