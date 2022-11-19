<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractUserEvent extends AbstractDomainEvent
{
    public function __construct(
        string $primaryId,
        private string $userId,
        ?DateTimeImmutable $occurredOn = null)
    {
        parent::__construct($primaryId, $occurredOn);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
