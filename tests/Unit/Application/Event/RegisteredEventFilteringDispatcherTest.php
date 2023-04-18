<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Application\Event\RegisteredEventFilteringDispatcher;
use Profesia\DddBackbone\Domain\Event\DispatcherInterface;
use Profesia\DddBackbone\Test\Assets\NullEvent;

class RegisteredEventFilteringDispatcherTest extends MockeryTestCase
{
    public function testCanDispatch(): void
    {
        $event = new NullEvent(
            '1'
        );

        /** @var DispatcherInterface|MockInterface $decoratedDispatcher */
        $decoratedDispatcher = Mockery::mock(DispatcherInterface::class);
        $decoratedDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->withArgs(
                [
                    $event
                ]
            );

        /** @var MetadataRegistry|MockInterface $metadataRegistry */
        $metadataRegistry = Mockery::mock(
            MetadataRegistry::class
        );

        $metadataRegistry
            ->shouldReceive('hasEventMetadata')
            ->once()
            ->withArgs(
                [
                    $event
                ]
            )->andReturn(true);

        $dispatcher = new RegisteredEventFilteringDispatcher(
            $metadataRegistry,
            $decoratedDispatcher
        );

        $dispatcher->dispatch(
            $event
        );
    }

    public function testCanDetectNonRegisteredEventMetadata(): void
    {
        $event = new NullEvent(
            '2'
        );

        /** @var DispatcherInterface|MockInterface $decoratedDispatcher */
        $decoratedDispatcher = Mockery::mock(DispatcherInterface::class);
        $decoratedDispatcher
            ->shouldReceive('dispatch');

        /** @var MetadataRegistry|MockInterface $metadataRegistry */
        $metadataRegistry = Mockery::mock(
            MetadataRegistry::class
        );

        $metadataRegistry
            ->shouldReceive('hasEventMetadata')
            ->once()
            ->withArgs(
                [
                    $event
                ]
            )->andReturn(false);

        $dispatcher = new RegisteredEventFilteringDispatcher(
            $metadataRegistry,
            $decoratedDispatcher
        );

        $dispatcher->dispatch(
            $event
        );
    }
}