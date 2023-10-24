<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Event;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Event\Exception\BadMetadataKeyException;
use Profesia\DddBackbone\Application\Event\Exception\MissingEventMetadataException;
use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\DddBackbone\Test\Assets\NullB2BEvent;
use Profesia\DddBackbone\Test\Assets\NullB2CEvent;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\DddBackbone\Test\Assets\NullEventWithOverriddenName;
use Profesia\DddBackbone\Test\Assets\NullMessageBroker;
use Profesia\DddBackbone\Test\Assets\NullUnregisteredEvent;

class MetadataRegistryTest extends TestCase
{
    public function testCanRegisterEventMetadata(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $event          = new NullB2CEvent(
            '1',
            '100'
        );
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                $event::getEventName() => [
                    'resource' => 'resource',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        $metadata = $registry->getEventMetadata($event);
        $this->assertEquals('resource', $metadata->getResource());
        $this->assertEquals($globalTarget, $metadata->getTarget());
        $this->assertEquals($globalProvider, $metadata->getProvider());
    }

    public function testCanDetectNonExistingClassDuringRegistration(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';

        $eventName = 'nonExistingClassName';
        $this->expectExceptionObject(
            new BadMetadataKeyException("Supplied string: [$eventName] is not a loadable class")
        );

        MetadataRegistry::createFromArrayConfig(
            [
                $eventName => [
                    'resource' => 'resource',
                ],
            ],
            $globalProvider,
            $globalTarget
        );
    }

    public function testCanDetectClassNotDescendingOfRequiredDomainClass(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';

        $eventName   = NullMessageBroker::class;
        $parentClass = AbstractDomainEvent::class;
        $this->expectExceptionObject(
            new BadMetadataKeyException("Class: [$eventName] is not a descendant of [$parentClass] class")
        );

        MetadataRegistry::createFromArrayConfig(
            [
                $eventName => [
                    'resource' => 'resource',
                ],
            ],
            $globalProvider,
            $globalTarget
        );
    }

    public function testCanDetectUnRegisteredEventMetadata(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $events         = [
            1 => new NullB2CEvent('1', '100'),
            2 => new NullB2BEvent('2', '101'),
            3 => new NullEvent('3'),
        ];
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                $events[1]::getEventName() => [
                    'resource' => 'resource1',
                ],
                $events[2]::getEventName() => [
                    'resource' => 'resource2',
                ],
                $events[3]::getEventName() => [
                    'resource' => 'resource3',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        for ($i = 1; $i <= 3; $i++) {
            $metadata = $registry->getEventMetadata($events[$i]);
            $this->assertEquals("resource{$i}", $metadata->getResource());
            $this->assertEquals($globalTarget, $metadata->getTarget());
            $this->assertEquals($globalProvider, $metadata->getProvider());
        }

        $this->expectExceptionObject(
            new MissingEventMetadataException('Metadata for event: [Profesia\DddBackbone\Test\Assets\NullUnregisteredEvent] are not registered')
        );
        $registry->getEventMetadata(new NullUnregisteredEvent('500'));
    }

    public function testCanOverrideGlobalTarget(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $events         = [
            1 => new NullB2CEvent('1', '100'),
            2 => new NullB2BEvent('2', '101'),
            3 => new NullEvent('3'),
        ];
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                $events[1]::getEventName() => [
                    'resource' => 'resource1',
                ],
                $events[2]::getEventName() => [
                    'resource'       => 'resource2',
                    'targetOverride' => 'targetOverride',
                ],
                $events[3]::getEventName() => [
                    'resource' => 'resource3',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        for ($i = 1; $i <= 3; $i++) {
            $metadata = $registry->getEventMetadata($events[$i]);
            $this->assertEquals("resource{$i}", $metadata->getResource());
            if ($i !== 2) {
                $this->assertEquals($globalTarget, $metadata->getTarget());
            } else {
                $this->assertEquals('targetOverride', $metadata->getTarget());
            }
            $this->assertEquals($globalProvider, $metadata->getProvider());
        }
    }

    public function testCanOverrideGetEventName(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $events         = [
            1 => new NullEventWithOverriddenName('1'),
        ];
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                get_class($events[1]) => [
                    'resource' => 'resource1',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        $metadata = $registry->getEventMetadata($events[1]);
        $this->assertEquals("resource1", $metadata->getResource());
        $this->assertEquals($globalTarget, $metadata->getTarget());
        $this->assertEquals($globalProvider, $metadata->getProvider());
    }

    public function testCanResolveIsPublicField(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $event          = new NullB2CEvent(
            '1',
            '100'
        );
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                $event::getEventName() => [
                    'resource' => 'resource',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        $metadata = $registry->getEventMetadata($event);
        $this->assertEquals('resource', $metadata->getResource());
        $this->assertEquals($globalTarget, $metadata->getTarget());
        $this->assertEquals($globalProvider, $metadata->getProvider());
        $this->assertNull($metadata->getTopic());

        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $event          = new NullB2CEvent(
            '1',
            '100'
        );
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                $event::getEventName() => [
                    'resource' => 'resource',
                    'topic'    => 'topic',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        $metadata = $registry->getEventMetadata($event);
        $this->assertEquals('resource', $metadata->getResource());
        $this->assertEquals($globalTarget, $metadata->getTarget());
        $this->assertEquals($globalProvider, $metadata->getProvider());
        $this->assertEquals('topic', $metadata->getTopic());
    }
}
