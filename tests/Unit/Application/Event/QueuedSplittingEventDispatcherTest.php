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
        /** @var MessageBrokerInterface|MockInterface $publicMessageBroker */
        $publicMessageBroker = Mockery::mock(MessageBrokerInterface::class);
        $publicMessageBroker
            ->shouldNotHaveBeenCalled();

        /** @var MessageBrokerInterface|MockInterface $privateMessageBroker */
        $privateMessageBroker = Mockery::mock(MessageBrokerInterface::class);
        $privateMessageBroker
            ->shouldNotHaveBeenCalled();

        /** @var MessageFactory|MockInterface $messageFactory */
        $messageFactory = Mockery::mock(MessageFactory::class);
        $messageFactory
            ->shouldNotHaveBeenCalled();

        $channel       = 'channel';
        $correlationId = 'correlation-id';
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $publicMessageBroker,
            $privateMessageBroker,
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
        /** @var MessageBrokerInterface|MockInterface $publicMessageBroker */
        $publicMessageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MessageBrokerInterface|MockInterface $privateMessageBroker */
        $privateMessageBroker = Mockery::mock(MessageBrokerInterface::class);
        $privateMessageBroker
            ->shouldNotHaveBeenCalled();

        /** @var MessageFactory|MockInterface $messageFactory */
        $messageFactory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = 'correlation-id';
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $publicMessageBroker,
            $privateMessageBroker,
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

        $publicMessageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                function (MessageCollection $collection) use ($messages) {
                    foreach ($collection as $key => $collectionMessage) {
                        if ($collectionMessage->toArray() !== $messages[$key]->toArray()) {
                            return false;
                        }
                    }

                    return true;
                }
            )->andReturn(
                $batchResponse
            );

        $dispatcher->flush();
    }

    public function testCanSplitMessagesIntoSeparateBrokers(): void
    {
        /** @var MessageBrokerInterface|MockInterface $publicMessageBroker */
        $publicMessageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MessageBrokerInterface|MockInterface $privateMessageBroker */
        $privateMessageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $publicMessageBroker,
            $privateMessageBroker,
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
            true
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
            false
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

        $publicMessageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                function (MessageCollection $collection) use ($messages, $channel) {
                    if ($channel !== $collection->getChannel()) {
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

        $privateMessageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                function (MessageCollection $collection) use ($messages, $channel) {
                    if ($channel !== $collection->getChannel()) {
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
        /** @var MessageBrokerInterface|MockInterface $publicMessageBroker */
        $publicMessageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MessageBrokerInterface|MockInterface $privateMessageBroker */
        $privateMessageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $publicMessageBroker,
            $privateMessageBroker,
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
                ($i % 2 === 0)
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

        $isPublicCallback  = function (Message $message) {
            return $message->isPublic();
        };
        $isPrivateCallback = function (Message $message) {
            return ($message->isPublic() === false);
        };
        $counter           = 0;
        $publicMessageBroker
            ->shouldReceive('publish')
            ->times(3)
            ->withArgs(
                function (MessageCollection $collection) use ($channel, $messages, &$counter, $isPublicCallback): bool {
                    if ($channel !== $collection->getChannel()) {
                        return false;
                    }

                    $offset = $counter * 20;
                    $slice  = array_filter(array_slice($messages, $offset, 20), $isPublicCallback);
                    $counter++;

                    return (array_values(array_filter($collection->getMessages(), $isPublicCallback)) === array_values($slice));
                }
            )->andReturn(
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_filter(array_slice($messages, 0, 20), $isPublicCallback)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_filter(array_slice($messages, 20, 20), $isPublicCallback)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_filter(array_slice($messages, 40, 5), $isPublicCallback)
                )
            );

        $secondCounter = 0;
        $privateMessageBroker
            ->shouldReceive('publish')
            ->times(3)
            ->withArgs(
                function (MessageCollection $collection) use ($channel, $messages, &$secondCounter, $isPrivateCallback): bool {
                    if ($channel !== $collection->getChannel()) {
                        return false;
                    }

                    $offset = $secondCounter * 20;
                    $slice  = array_filter(array_slice($messages, $offset, 20), $isPrivateCallback);
                    $secondCounter++;

                    return (array_values(array_filter($collection->getMessages(), $isPrivateCallback)) === array_values($slice));
                }
            )->andReturn(
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_filter(array_slice($messages, 0, 20), $isPrivateCallback)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_filter(array_slice($messages, 20, 20), $isPrivateCallback)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                    true,
                    'status',
                    ...array_filter(array_slice($messages, 40, 5), $isPrivateCallback)
                )
            );

        $dispatcher->flush();
    }

    public function testCanSendFewerMessagesThanBatchSize(): void
    {
        /** @var MessageBrokerInterface|MockInterface $publicMessageBroker */
        $publicMessageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MessageBrokerInterface|MockInterface $privateMessageBroker */
        $privateMessageBroker = Mockery::mock(MessageBrokerInterface::class);
        $privateMessageBroker
            ->shouldNotHaveBeenCalled();

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedSplittingEventDispatcher(
            $publicMessageBroker,
            $privateMessageBroker,
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

        $publicMessageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                function (MessageCollection $collection) use ($channel, $messages): bool {
                    if ($channel !== $collection->getChannel()) {
                        return false;
                    }

                    return (array_values($collection->getMessages()) === array_values($messages));
                }
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