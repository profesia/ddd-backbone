<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Application\Message;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Application\Messaging\AwsMessageFactory;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\MessagingCore\Broking\Dto\Sending\AwsMessage;

class AwsMessageFactoryTest extends TestCase
{
    public function testCanCreateMessageFromDomainEvent(): void
    {
        $globalProvider = 'globalProvider';
        $event          = new NullEvent('8d0e43fd-d5d4-4b61-8963-e777c591cf0d');
        $config         = [
            'resource' => 'resource',
            'topic'    => 'topic',
        ];

        $registry = MetadataRegistry::createFromArrayConfig(
            [
                $event::getEventName() => $config,
            ],
            $globalProvider
        );

        $factory = new AwsMessageFactory(
            $registry
        );

        $message     = $factory->createFromDomainEvent($event, 'correlation-id');
        $messageData = $message->toArray();
        $this->assertEquals('8d0e43fd-d5d4-4b61-8963-e777c591cf0d', $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_OBJECT_ID]);
        $this->assertEquals('correlation-id', $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_CORRELATION_ID]);
        $this->assertEquals($config['resource'], $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_RESOURCE]);
        $this->assertEquals($globalProvider, $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_PROVIDER]);
        $this->assertEquals($config['topic'], $message->getTopic());
    }

    public function testCanCreateMessageFromDomainEventWithOverride(): void
    {
        $globalProvider = 'globalProvider';
        $event          = new NullEvent('8d0e43fd-d5d4-4b61-8963-e777c591cf0d');
        $config         = [
            'resource' => 'resource',
            'provider' => 'provider',
            'topic'    => 'topic',
        ];

        $registry = MetadataRegistry::createFromArrayConfig(
            [
                $event::getEventName() => $config,
            ],
            $globalProvider
        );

        $factory = new AwsMessageFactory(
            $registry
        );

        $message     = $factory->createFromDomainEvent($event, 'correlation-id');
        $messageData = $message->toArray();
        $this->assertEquals('8d0e43fd-d5d4-4b61-8963-e777c591cf0d', $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_OBJECT_ID]);
        $this->assertEquals('correlation-id', $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_CORRELATION_ID]);
        $this->assertEquals($config['resource'], $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_RESOURCE]);
        $this->assertEquals($config['provider'], $messageData[AwsMessage::DETAIL][AwsMessage::EVENT_PROVIDER]);
        $this->assertEquals($config['topic'], $message->getTopic());
    }
}
