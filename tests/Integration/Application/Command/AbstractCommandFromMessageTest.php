<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Application\Command;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Command\Factory\CommandMapFromMessagesFactory;
use Profesia\DddBackbone\Test\Assets\NullCommand;
use Profesia\MessagingCore\Broking\Dto\Message;
use Profesia\MessagingCore\Broking\Dto\PubSubReceivedMessage;

class AbstractCommandFromMessageTest extends TestCase
{
    public function testCanDecodedReceivedMessage(): void
    {
        $factory   = new CommandMapFromMessagesFactory();
        $eventType = 'eventType1';
        $factory->registerCommandClass('*', NullCommand::class);

        $message  = [
            'message' => [
                Message::EVENT_ATTRIBUTES => [
                    MESSAGE::EVENT_TYPE => $eventType,
                ],
                Message::EVENT_DATA       =>
                    base64_encode(
                        json_encode(
                            ['test' => true]
                        )
                    ),
            ]
        ];
        $instance = $factory->createFromReceivedMessage(
            PubSubReceivedMessage::createFromJsonString(json_encode($message))
        );

        $expectedMessage = $message['message'];
        $expectedMessage[Message::EVENT_DATA] = json_decode(base64_decode($expectedMessage[Message::EVENT_DATA]), true);
        $this->assertEquals(
            ['NullCommand' => $expectedMessage],
            $instance->getPayload()
        );
    }
}