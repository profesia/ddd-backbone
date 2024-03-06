<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Application\Message;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Application\Messaging\PubSubMessageFactory;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\MessagingCore\Broking\Dto\Sending\PubSubMessage;

class PubSubMessageFactoryTest extends TestCase
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

        $factory = new PubSubMessageFactory(
            $registry
        );

        $message     = $factory->createFromDomainEvent($event, 'correlation-id');
        $messageData = $message->toArray();
        $this->assertEquals('8d0e43fd-d5d4-4b61-8963-e777c591cf0d', $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_OBJECT_ID]);
        $this->assertEquals('correlation-id', $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_CORRELATION_ID]);
        $this->assertEquals($config['resource'], $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_RESOURCE]);
        $this->assertEquals($globalProvider, $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_PROVIDER]);
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

        $factory = new PubSubMessageFactory(
            $registry
        );

        $message     = $factory->createFromDomainEvent($event, 'correlation-id');
        $messageData = $message->toArray();
        $this->assertEquals('8d0e43fd-d5d4-4b61-8963-e777c591cf0d', $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_OBJECT_ID]);
        $this->assertEquals('correlation-id', $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_CORRELATION_ID]);
        $this->assertEquals($config['resource'], $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_RESOURCE]);
        $this->assertEquals($config['provider'], $messageData[PubSubMessage::EVENT_ATTRIBUTES][PubSubMessage::EVENT_PROVIDER]);
        $this->assertEquals($config['topic'], $message->getTopic());
    }
}
