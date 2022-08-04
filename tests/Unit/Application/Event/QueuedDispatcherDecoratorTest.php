<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\DddBackbone\Application\Event\QueuedDispatcherDecorator;
use Mockery\MockInterface;
use Mockery;
use Profesia\DddBackbone\Domain\Event\EventDispatcherInterface;
use Profesia\DddBackbone\Test\Integration\Domain\Event\TestEvent;

class QueuedDispatcherDecoratorTest extends MockeryTestCase
{
    public function testCanQueueEvents(): void
    {
        /** @var MockInterface|EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldNotHaveBeenCalled();

        $decorator = new QueuedDispatcherDecorator(
            $eventDispatcher
        );

        $decorator->dispatch(
            new TestEvent(1)
        );
    }

    public function testCanFlush(): void
    {
        /** @var MockInterface|EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $decorator = new QueuedDispatcherDecorator(
            $eventDispatcher
        );

        $events = [
            new TestEvent(1),
            new TestEvent(2),
            new TestEvent(3),
        ];

        foreach($events as $event) {
            $eventDispatcher
                ->shouldReceive('dispatch')
                ->once()
                ->withArgs(
                    [
                        $event
                    ]
                );

            $decorator->dispatch($event);
        }

        $decorator->flush();
    }
}
