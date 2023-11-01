<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Event;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Application\Event\QueuedEventDispatcher;
use Profesia\DddBackbone\Application\Messaging\MessageFactory;
use Profesia\DddBackbone\Test\Assets\NullMessageBroker;

class QueuedEventDispatcherTest extends TestCase
{
    public function provideDataForBatchSizeDetecting(): array
    {
        return [
            [
                -1,
                'Batch size should be in the interval <1,1000>'
            ],
            [
                0,
                'Batch size should be in the interval <1,1000>'
            ],
            [
                10,
                null
            ],
            [
                1000,
                null
            ],
            [
                1001,
                'Batch size should be in the interval <1,1000>'
            ]
        ];
    }

    /**
     * @param int    $batchSize
     * @param null|string $errorMessage
     *
     * @return void
     * @dataProvider provideDataForBatchSizeDetecting
     */
    public function testCanCorrectlyDetectBatchSize(int $batchSize, ?string $errorMessage = null): void
    {
        if ($errorMessage !== null) {
            $this->expectExceptionObject(
                new RuntimeException(
                    $errorMessage
                )
            );
        }

        $dispatcher = new QueuedEventDispatcher(
            new NullMessageBroker(),
            new MessageFactory(
                MetadataRegistry::createFromArrayConfig(
                    [],
                    'provider',
                )
            ),
            'correlationId',
            $batchSize
        );

        $this->assertTrue(true);
    }
}
