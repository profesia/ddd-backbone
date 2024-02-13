<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractDomainEvent
{
    private string             $primaryId;
    private DateTimeImmutable $occurredOn;


    public function __construct(
        string $primaryId,
        ?DateTimeImmutable $occurredOn = null
    )
    {
        if ($occurredOn === null) {
            $this->occurredOn = new DateTimeImmutable();
        } else {
            $this->occurredOn = $occurredOn;
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

    public function getPublicName(): string
    {
        $fullName = static::class;
        $position = strrpos($fullName, '\\');
        if ($position === false) {
            $position = 0;
        } else {
            $position++;
        }

        return substr(
            $fullName,
            $position
        );
    }

    public static function getEventName(): string
    {
        return static::class;
    }
}

