<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractDomainEvent
{
    private string $primaryId;
    private ?DateTimeImmutable $occurredOn = null;


    public function __construct(
        string $primaryId,
        ?DateTimeImmutable $occurredOn = null
    )
    {
        $this->occurredOn = $occurredOn;
        if ($this->occurredOn === null) {
            $this->occurredOn = new DateTimeImmutable();
        }

        $this->primaryId = $primaryId;
    }

    public function getPrimaryId(): string
    {
        return $this->primaryId;
    }

    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public abstract function getPayload(): array;
}

