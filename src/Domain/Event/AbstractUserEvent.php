<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

use DateTimeImmutable;

abstract class AbstractUserEvent extends AbstractDomainEvent
{
    private string $userId;

    public function __construct(
        string $primaryId,
        string $userId,
        ?DateTimeImmutable $occurredOn = null
    ) {
        parent::__construct($primaryId, $occurredOn);
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
