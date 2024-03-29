<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Event;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Test\Assets\NullB2CEvent;

class CustomerEventTest extends TestCase
{
    public function testEvent(): void
    {
        $occurredOn = new DateTimeImmutable();
        $event      = new NullB2CEvent(
            '1',
            '100',
            $occurredOn
        );

        $this->assertEquals('1', $event->getPrimaryId());
        $this->assertEquals('100', $event->getUserId());
        $this->assertEquals($occurredOn, $event->getOccurredOn());
        $this->assertEquals(
            [
                'occurredOn' => $occurredOn->format('Y-m-d H:i:s'),
                'primaryId'  => '1',
                'userId'     => '100',
            ],
            $event->getPayload()
        );
    }
}
