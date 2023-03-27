<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractDomainEvent
{

    public function __construct(
        private string $primaryId,
        private ?DateTimeImmutable $occurredOn = null
    )
    {
        if ($this->occurredOn === null) {
            $this->occurredOn = new DateTimeImmutable();
        }
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

    public static function getEventName(): string
    {
        return self::class;
    }
}

