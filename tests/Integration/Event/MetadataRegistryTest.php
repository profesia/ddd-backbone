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
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                'eventName' => [
                    'resource' => 'resource',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        $metadata = $registry->getEventMetadata('eventName');
        $this->assertEquals('resource', $metadata->getResource());
        $this->assertEquals($globalTarget, $metadata->getTarget());
        $this->assertEquals($globalProvider, $metadata->getProvider());
    }

    public function testCanDetectUnRegisteredEventMetadata(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                'eventName1' => [
                    'resource' => 'resource1',
                ],
                'eventName2' => [
                    'resource' => 'resource2',
                ],
                'eventName3' => [
                    'resource' => 'resource3',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        for ($i = 1; $i <= 3; $i++) {
            $metadata = $registry->getEventMetadata("eventName{$i}");
            $this->assertEquals("resource{$i}", $metadata->getResource());
            $this->assertEquals($globalTarget, $metadata->getTarget());
            $this->assertEquals($globalProvider, $metadata->getProvider());
        }

        $this->expectExceptionObject(
            new MissingEventMetadataException('Metadata for event: [unregisteredEvent] are not registered')
        );
        $registry->getEventMetadata('unregisteredEvent');
    }

    public function testCanOverrideGlobalTarget(): void
    {
        $globalTarget   = 'globalTarget';
        $globalProvider = 'globalProvider';
        $registry       = MetadataRegistry::createFromArrayConfig(
            [
                'eventName1' => [
                    'resource' => 'resource1',
                ],
                'eventName2' => [
                    'resource'       => 'resource2',
                    'targetOverride' => 'targetOverride',
                ],
                'eventName3' => [
                    'resource' => 'resource3',
                ],
            ],
            $globalProvider,
            $globalTarget
        );

        for ($i = 1; $i <= 3; $i++) {
            $metadata = $registry->getEventMetadata("eventName{$i}");
            $this->assertEquals("resource{$i}", $metadata->getResource());
            if ($i !== 2) {
                $this->assertEquals($globalTarget, $metadata->getTarget());
            } else {
                $this->assertEquals('targetOverride', $metadata->getTarget());
            }
            $this->assertEquals($globalProvider, $metadata->getProvider());
        }
    }
}
