<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\DddBackbone\Application\Event\EventMetadata;
use Profesia\DddBackbone\Application\Event\Exception\BadEventMetadataConfigurationExceptionAbstract;
use \Profesia\DddBackbone\Application\Exception\AbstractApplicationException;

class EventMetadataTest extends MockeryTestCase
{
    public function provideConfig(): array
    {
        return [
            [
                [
                ],
                new BadEventMetadataConfigurationExceptionAbstract('Key: [resource] is missing in the supplied config'),
            ],
            [
                [
                    'resource' => 'resource1',
                ],
                new BadEventMetadataConfigurationExceptionAbstract('Key: [provider] is missing in the supplied config'),
            ],
            [
                [
                    'resource' => 'resource2',
                    'provider' => 'provider2',
                ],
                new BadEventMetadataConfigurationExceptionAbstract('Key: [target] is missing in the supplied config'),
            ],
            [
                [
                    'resource' => 'resource3',
                    'provider' => 'provider3',
                    'target'   => 'target3',
                ],
            ],
            [
                [
                    'resource' => 'resource4',
                    'provider' => 'provider4',
                    'target'   => 'target4',
                    'isPublic' => true,
                ],
            ],
            [
                [
                    'resource' => 'resource5',
                    'provider' => 'provider5',
                    'target'   => 'target5',
                    'isPublic' => false,
                ],
            ],
            [
                [
                    'resource'    => 'resource6',
                    'provider'    => 'provider6',
                    'target'      => 'target6',
                    'keyToIgnore' => 'value',
                ],
            ],
        ];
    }

    /**
     * @param array                             $config
     * @param AbstractApplicationException|null $exception
     *
     * @return void
     * @dataProvider provideConfig
     */
    public function testCanIdentifyMissingConfig(array $config, ?AbstractApplicationException $exception = null): void
    {
        $hasException = ($exception !== null);
        if ($hasException === true) {
            $this->expectExceptionObject($exception);
        }

        $metadata = EventMetadata::createFromArray($config);
        if ($hasException === false) {
            $this->assertEquals($config['resource'], $metadata->getResource());
            $this->assertEquals($config['target'], $metadata->getTarget());
            $this->assertEquals($config['provider'], $metadata->getProvider());

            $isPublic = true;
            if (array_key_exists('isPublic', $config) === true) {
                $isPublic = $config['isPublic'];
            }

            $this->assertEquals($isPublic, $metadata->isPublic());
        }
    }
}
