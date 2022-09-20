<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractDomainEvent
{

    public function __construct(
        private ?DateTimeImmutable $occurredOn = null
    )
    {
        if ($this->occurredOn === null) {
            $this->occurredOn = new DateTimeImmutable();
        }
    }

    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public abstract function getPayload(): array;
}

