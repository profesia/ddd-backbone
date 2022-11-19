<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Event;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Test\NullB2BEvent;

class BusinessEventTest extends TestCase
{
    public function testEvent(): void
    {
        $occurredOn = new DateTimeImmutable();
        $event      = new NullB2BEvent(
            '1',
            '100',
            $occurredOn
        );

        $this->assertEquals('1', $event->getPrimaryId());
        $this->assertEquals('100', $event->getCompanyId());
        $this->assertEquals($occurredOn, $event->getOccurredOn());
        $this->assertEquals(
            [
                'occurredOn' => $occurredOn->format('Y-m-d H:i:s'),
                'primaryId'  => '1',
                'businessId' => '100',
            ],
            $event->getPayload()
        );
    }
}
