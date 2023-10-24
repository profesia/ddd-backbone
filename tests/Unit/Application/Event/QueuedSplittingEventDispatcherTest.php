<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Event\QueuedSplittingEventDispatcher;
use Profesia\DddBackbone\Application\Messaging\MessageFactory;
use Profesia\DddBackbone\Test\Assets\NullB2CEvent;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\MessagingCore\Broking\Dto\BrokingBatchResponse;
use Profesia\MessagingCore\Broking\Dto\Message;
use Profesia\MessagingCore\Broking\Dto\MessageCollection;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;
use Ramsey\Uuid\Uuid;

class QueuedSplittingEventDispatcherTest extends MockeryTestCase
{
    public function testCanAddEventsToQueue(): void
    {
        /** @var MessageBrokerInterface|MockInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);
        $messageBroker
            ->shouldNotHaveBeenCalled();

        /** @var MessageFactory|MockInterface $messageFactory */
        $messageFactory = Mockery::mock(MessageFactory::class);
        $messageFactory
            ->shouldNotHaveBeenCalled();

        $channel       = 'channel';
        $correlationId = 'correlation-id';
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $messageBroker,
            $messageFactory,
            $channel,
            $correlationId
        );

        $objectId = 'object-id';
        $dateTime = new DateTimeImmutable();
        $dispatcher->dispatch(
            new NullEvent(
                $objectId,
                $dateTime
            )
        );
    }

    public function testCanFlush(): void
    {
        /** @var MessageBrokerInterface|MockInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MessageFactory|MockInterface $messageFactory */
        $messageFactory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = 'correlation-id';
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $messageBroker,
            $messageFactory,
            $channel,
            $correlationId
        );

        $objectIdBase = 'object-id';
        $messages     = [];
        for ($i = 1; $i <= 5; $i++) {
            $dateTime = new DateTimeImmutable();
            $objectId = "{$objectIdBase}-{$i}";
            $event    = new NullEvent(
                $objectId,
                $dateTime
            );

            $dispatcher->dispatch(
                $event
            );

            $message = new Message(
                "resource-{$i}",
                get_class($event),
                "provider-{$i}",
                $objectId,
                $dateTime,
                $correlationId,
                "target-{$i}",
                "publicName-{$i}",
                $event->getPayload()
            );
            $messageFactory
                ->shouldReceive('createFromDomainEvent')
                ->once()
                ->withArgs(
                    [
                        $event,
                        $correlationId,
                    ]
                )->andReturn(
                    $message
                );

            $messages[] = $message;
        }

        $batchResponse = BrokingBatchResponse::createForMessagesWithBatchStatus(
            true,
            null,
            ...$messages
        );

        $messageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                static function (MessageCollection $collection) use ($messages, $channel): bool {
                    foreach ($collection as $key => $collectionMessage) {
                        if ($collectionMessage->toArray() !== $messages[$key]->toArray()) {
                            return false;
                        }
                    }

                    return $collection->getChannel() === $channel;
                }
            )->andReturn(
                $batchResponse
            );

        $dispatcher->flush();
    }

    public function testCanSplitMessagesIntoSeparateTopics(): void
    {
        /** @var MessageBrokerInterface|MockInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $messageBroker,
            $factory,
            $channel,
            $correlationId,
            100
        );

        $events      = [];
        $messages    = [];
        $events[1]   = new NullB2CEvent(
            '1',
            '101'
        );
        $events[2]   = new NullB2CEvent(
            '2',
            '102'
        );
        $messages[1] = new Message(
            'Resource',
            'EventType',
            'Provider',
            '1',
            new DateTimeImmutable(),
            $correlationId,
            'Target',
            'PublicName',
            $events[1]->getPayload(),
            'topicName1'
        );
        $messages[2] = new Message(
            'Resource',
            'EventType',
            'Provider',
            '2',
            new DateTimeImmutable(),
            $correlationId,
            'Target',
            'PublicName',
            $events[2]->getPayload(),
            'topicName2'
        );

        $factory
            ->shouldReceive('createFromDomainEvent')
            ->once()
            ->withArgs(
                [
                    $events[1],
                    $correlationId,
                ]
            )->andReturn(
                $messages[1]
            );

        $factory
            ->shouldReceive('createFromDomainEvent')
            ->once()
            ->withArgs(
                [
                    $events[2],
                    $correlationId,
                ]
            )->andReturn(
                $messages[2]
            );

        $messageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                static function (MessageCollection $collection) use ($messages, $channel): bool {
                    if ('topicName1' !== $collection->getChannel()) {
                        return false;
                    }

                    return ($collection->getMessages() === [$messages[1]]);
                }
            )->andReturn(
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    $messages[1]
                ),
            );

        $messageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                static function (MessageCollection $collection) use ($messages, $channel): bool {
                    if ('topicName2' !== $collection->getChannel()) {
                        return false;
                    }

                    return ($collection->getMessages() === [$messages[2]]);
                }
            )->andReturn(
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    $messages[2]
                ),
            );

        $dispatcher->dispatch(
            $events[1]
        );
        $dispatcher->dispatch(
            $events[2]
        );

        $dispatcher->flush();
    }

    public function testCanSplitMessagesIntoBatches(): void
    {
        /** @var MessageBrokerInterface|MockInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $messageBroker,
            $factory,
            $channel,
            $correlationId,
            20
        );

        $events   = [];
        $messages = [];
        for ($i = 1; $i <= 45; $i++) {
            $events[$i]   = new NullB2CEvent(
                (string)$i,
                (string)($i + 100)
            );
            $messages[$i] = new Message(
                'Resource',
                'EventType',
                'Provider',
                (string)$i,
                new DateTimeImmutable(),
                $correlationId,
                'Target',
                'PublicName',
                $events[$i]->getPayload(),
                ($i % 2 === 1 ? 'topicName1' : 'topicName2')
            );

            $dispatcher->dispatch(
                $events[$i]
            );

            $factory
                ->shouldReceive('createFromDomainEvent')
                ->once()
                ->withArgs(
                    [
                        $events[$i],
                        $correlationId,
                    ]
                )->andReturn(
                    $messages[$i]
                );
        }

        $isTopic1Callback = static fn(Message $message): bool => $message->getTopic() === 'topicName1';
        $isTopic2Callback = static fn(Message $message): bool => $message->getTopic() === 'topicName2';
        $counter1         = $counter2 = 0;
        $messageBroker
            ->shouldReceive('publish')
            ->times(4)
            ->withArgs(
                static function (MessageCollection $collection) use ($channel, $messages, &$counter1, &$counter2, $isTopic1Callback, $isTopic2Callback): bool {
                    if ($collection->getChannel() === 'topicName1') {
                        $callback = $isTopic1Callback;
                        $offset   = $counter1 * 20;
                        $counter1++;
                    } else {
                        $callback = $isTopic2Callback;
                        $offset   = $counter2 * 20;
                        $counter2++;
                    }

                    return array_values(array_filter($collection->getMessages(), $callback)) === array_values(array_slice(array_filter($messages, $callback), $offset, 20));
                }
            )->andReturn(
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_slice(array_filter($messages, $isTopic1Callback), 0, 20)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_slice(array_filter($messages, $isTopic2Callback), 0, 20)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_slice(array_filter($messages, $isTopic1Callback), 20, 20)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_slice(array_filter($messages, $isTopic2Callback), 20, 20)
                ),
            );

        $dispatcher->flush();
    }

    public function testCanSendFewerMessagesThanBatchSize(): void
    {
        /** @var MessageBrokerInterface|MockInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $messageBroker,
            $factory,
            $channel,
            $correlationId,
            100
        );

        $events   = [];
        $messages = [];
        for ($i = 1; $i <= 20; $i++) {
            $events[$i]   = new NullB2CEvent(
                (string)$i,
                (string)($i + 100)
            );
            $messages[$i] = new Message(
                'Resource',
                'EventType',
                'Provider',
                (string)$i,
                new DateTimeImmutable(),
                $correlationId,
                'Target',
                'PublicName',
                $events[$i]->getPayload()
            );

            $dispatcher->dispatch(
                $events[$i]
            );

            $factory
                ->shouldReceive('createFromDomainEvent')
                ->once()
                ->withArgs(
                    [
                        $events[$i],
                        $correlationId,
                    ]
                )->andReturn(
                    $messages[$i]
                );
        }

        $messageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                static fn(MessageCollection $collection): bool => array_values($collection->getMessages()) === array_values($messages)
            )->andReturn(
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...$messages
                ),
            );

        $dispatcher->flush();
    }
}