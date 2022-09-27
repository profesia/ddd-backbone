<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Event;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Event\Exception\MissingEventMetadataException;
use Profesia\DddBackbone\Application\Event\MetadataRegistry;

class MetadataRegistryTest extends TestCase
{
    public function testCanRegisterEventMetadata(): void
    {
        $registry = MetadataRegistry::createFromArrayConfig(
            [
                'eventName' => [
                    'resource' => 'resource',
                    'target'   => 'target',
                    'provider' => 'provider',
                ],
            ]
        );

        $metadata = $registry->getEventMetadata('eventName');
        $this->assertEquals('resource', $metadata->getResource());
        $this->assertEquals('target', $metadata->getTarget());
        $this->assertEquals('provider', $metadata->getProvider());
    }

    public function testCanDetectUnRegisteredEventMetadata(): void
    {
        $registry = MetadataRegistry::createFromArrayConfig(
            [
                'eventName1' => [
                    'resource' => 'resource1',
                    'target'   => 'target1',
                    'provider' => 'provider1',
                ],
                'eventName2' => [
                    'resource' => 'resource2',
                    'target'   => 'target2',
                    'provider' => 'provider2',
                ],
                'eventName3' => [
                    'resource' => 'resource3',
                    'target'   => 'target3',
                    'provider' => 'provider3',
                ],
            ]
        );

        for ($i = 1; $i <= 3; $i++) {
            $metadata = $registry->getEventMetadata("eventName{$i}");
            $this->assertEquals("resource{$i}", $metadata->getResource());
            $this->assertEquals("target{$i}", $metadata->getTarget());
            $this->assertEquals("provider{$i}", $metadata->getProvider());
        }

        $this->expectExceptionObject(
            new MissingEventMetadataException('Metadata for event: [unregisteredEvent] are not registered')
        );
        $registry->getEventMetadata('unregisteredEvent');
    }
}
