<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Domain\Event;

use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;

class TestEvent extends AbstractDomainEvent
{
    public function __construct(
        private int $data,
        ?DateTimeImmutable $occurredOn = null
    )
    {
        parent::__construct(
            $occurredOn
        );
    }

    public function getData(): int
    {
        return $this->data;
    }
}
