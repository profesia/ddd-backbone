<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Event\QueuedEventDispatcher;
use Profesia\DddBackbone\Application\Messaging\MessageFactoryInterface;
use Profesia\DddBackbone\Test\Assets\NullB2CEvent;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\DddBackbone\Test\Assets\NullMessage;
use Profesia\MessagingCoreContracts\Broking\Dto\Sending\BrokingBatchResponse;
use Profesia\MessagingCoreContracts\Broking\Dto\Sending\GroupedMessagesCollection;
use Profesia\MessagingCoreContracts\Broking\MessageBrokerInterface;
use Ramsey\Uuid\Uuid;

class QueuedEventDispatcherTest extends MockeryTestCase
{
    public function testCanAddEventsToQueue(): void
    {
        /** @var MessageBrokerInterface|MockInterface $messageBroker */
        $messageBroker = Mockery::mock(MessageBrokerInterface::class);
        $messageBroker
            ->shouldNotHaveBeenCalled();

        /** @var MessageFactoryInterface|MockInterface $messageFactory */
        $messageFactory = Mockery::mock(MessageFactoryInterface::class);
        $messageFactory
            ->shouldNotHaveBeenCalled();

        $correlationId = 'correlation-id';
        $dispatcher    = new QueuedEventDispatcher(
            $messageBroker,
            $messageFactory,
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

        /** @var MessageFactoryInterface|MockInterface $messageFactory */
        $messageFactory = Mockery::mock(MessageFactoryInterface::class);

        $correlationId = 'correlation-id';
        $dispatcher    = new QueuedEventDispatcher(
            $messageBroker,
            $messageFactory,
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

            $message = new NullMessage(
                "Topic-{$i}",
                ['objectId' => $objectId],
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

        $counter = 0;
        $messageBroker
            ->shouldReceive('publish')
            ->once()
            ->withArgs(
                function (GroupedMessagesCollection $collection) use ($messages, &$counter) {
                    foreach ($collection->getTopics() as $topic) {
                        foreach ($collection->getMessagesForTopic($topic) as $collectionMessage) {
                            if ($collectionMessage->toArray() !== $messages[$counter++]->toArray()) {
                                return false;
                            }
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

        /** @var MockInterface|MessageFactoryInterface $factory */
        $factory = Mockery::mock(MessageFactoryInterface::class);

        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedEventDispatcher(
            $messageBroker,
            $factory,
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
            $messages[$i] = new NullMessage(
                'Topic',
                ['objectId' => (string)$i],
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
                function (GroupedMessagesCollection $collection) use ($messages, &$counter): bool {
                    $offset = $counter * 20;
                    $slice  = array_slice($messages, $offset, 20);
                    $counter++;

                    return (array_values($collection->getMessagesForTopic('Topic')) === array_values($slice));
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

        /** @var MockInterface|MessageFactoryInterface $factory */
        $factory = Mockery::mock(MessageFactoryInterface::class);

        $correlationId = Uuid::uuid4()->toString();
        $dispatcher    = new QueuedEventDispatcher(
            $messageBroker,
            $factory,
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
            $messages[$i] = new NullMessage(
                'Topic',
                ['objectId' => (string)$i],
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
                function (GroupedMessagesCollection $collection) use ($messages): bool {
                    return (array_values($collection->getMessagesForTopic('Topic')) === array_values($messages));
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
