<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\DddBackbone\Domain\Event\DispatcherInterface;
use Profesia\DddBackbone\Test\Assets\NullB2BEvent;
use Profesia\DddBackbone\Test\Assets\NullB2CEvent;

class EventsTest extends MockeryTestCase
{
    public function provideEventsData(): array
    {
        $occurredOn = new DateTimeImmutable();
        return [
            [
                new NullB2CEvent('1', '100', $occurredOn),
                [
                    'getOccurredOn' => $occurredOn,
                    'getPrimaryId'  => '1',
                    'getUserId' => '100',
                 ]
            ],
            [
                new NullB2BEvent('2', '200', $occurredOn),
                [
                    'getOccurredOn' => $occurredOn,
                    'getPrimaryId'  => '2',
                    'getCompanyId' => '200'
                ]
            ]
        ];
    }

    /**
     * @param AbstractDomainEvent $event
     * @param array               $dataCalls
     *
     * @return void
     * @dataProvider provideEventsData
     */
    public function testEvents(AbstractDomainEvent $event, array $dataCalls): void
    {
        foreach ($dataCalls as $methodName => $expectedValue) {
            $this->assertEquals($expectedValue, $event->{$methodName}());
        }

        /** @var DispatcherInterface|MockInterface $dispatcher */
        $dispatcher = Mockery::mock(DispatcherInterface::class);
        $dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->withArgs(
                [
                    $event
                ]
            );

        $dispatcher->dispatch($event);
    }
}
