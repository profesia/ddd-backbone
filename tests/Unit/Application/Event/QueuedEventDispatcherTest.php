<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Event\QueuedEventDispatcher;
use Profesia\DddBackbone\Application\Messaging\MessageFactory;
use Profesia\DddBackbone\Test\Assets\NullB2CEvent;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\MessagingCore\Broking\Dto\BrokingBatchResponse;
use Profesia\MessagingCore\Broking\Dto\Message;
use Profesia\MessagingCore\Broking\Dto\MessageCollection;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;
use Ramsey\Uuid\Uuid;

class QueuedEventDispatcherTest extends MockeryTestCase
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
        $dispatcher    = new QueuedEventDispatcher(
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
        $dispatcher    = new QueuedEventDispatcher(
            $messageBroker,
            $messageFactory,
            $channel,
            $correlationId
        );

        $objectIdBase       = 'object-id';
        $messages           = [];
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

            $messages[]           = $message;
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

    public function testCanSplitMessagesIntoBatches(): void
    {
        /** @var MockInterface|MessageBrokerInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedEventDispatcher(
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

        $counter = 0;
        $messageBroker
            ->shouldReceive('publish')
            ->times(3)
            ->withArgs(
                function (MessageCollection $collection) use ($channel, $messages, &$counter): bool {
                    if ($channel !== $collection->getChannel()) {
                        return false;
                    }

                    $offset = $counter * 20;
                    $slice  = array_slice($messages, $offset, 20);
                    $counter++;

                    return (array_values($collection->getMessages()) === array_values($slice));
                }
            )->andReturn(
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                       true,
                       'status',
                    ...array_slice($messages, 0, 20)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                       true,
                       'status',
                    ...array_slice($messages, 20, 20)
                ),
                BrokingBatchResponse::createForMessagesWithBatchStatus(
                       true,
                       'status',
                    ...array_slice($messages, 40, 5)
                )
            );

        $dispatcher->flush();
    }

    public function testCanSendFewerMessagesThanBatchSize(): void
    {
        /** @var MockInterface|MessageBrokerInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);

        /** @var MockInterface|MessageFactory $factory */
        $factory = Mockery::mock(MessageFactory::class);

        $channel       = 'channel';
        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedEventDispatcher(
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
