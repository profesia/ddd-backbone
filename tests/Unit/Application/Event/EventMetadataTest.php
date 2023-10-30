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
                new BadEventMetadataConfigurationExceptionAbstract('Key: [publishingTopic] is missing in the supplied config'),
            ],
            [
                [
                    'resource'        => 'resource3',
                    'provider'        => 'provider3',
                    'publishingTopic' => 'publishingTopic3',
                ],
                new BadEventMetadataConfigurationExceptionAbstract('Key: [errorTopic] is missing in the supplied config'),
            ],
            [
                [
                    'resource'        => 'resource5',
                    'provider'        => 'provider5',
                    'publishingTopic' => 'publishingTopic5',
                    'errorTopic'      => 'errorTopic5'
                ],
            ],
            [
                [
                    'resource'        => 'resource6',
                    'provider'        => 'provider6',
                    'publishingTopic' => 'publishingTopic6',
                    'errorTopic'      => 'errorTopic6',
                    'keyToIgnore'     => 'value',
                ],
            ],
        ];
    }

    /**
     * @param array $config
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
            $this->assertEquals($config['provider'], $metadata->getProvider());
            $this->assertEquals($config['publishingTopic'], $metadata->getPublishingTopic());
            $this->assertEquals($config['errorTopic'], $metadata->getErrorTopic());
        }
    }
}
