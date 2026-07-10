<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Application\Command;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Command\Factory\CommandMapFromMessagesFactory;
use Profesia\DddBackbone\Test\Assets\NullCommand;
use Profesia\DddBackbone\Test\Assets\NullReceivedMessage;

class AbstractCommandFromMessageTest extends TestCase
{
    public function testCanDecodedReceivedMessage(): void
    {
        $factory = new CommandMapFromMessagesFactory();
        $factory->registerCommandClass('*', NullCommand::class);

        $decodedMessage = [
            'attributes' => [
                'eventType' => 'eventType1',
            ],
            'data'       => [
                'test' => true,
            ],
        ];

        $instance = $factory->createFromReceivedMessage(
            new NullReceivedMessage('eventType1', '*', $decodedMessage)
        );

        $this->assertEquals(
            ['NullCommand' => $decodedMessage],
            $instance->getPayload()
        );
    }
}
