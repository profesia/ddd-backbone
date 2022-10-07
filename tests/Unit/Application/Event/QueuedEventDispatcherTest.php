<?php

declare(strict_types=1);


namespace Profesia\DddBackbone\Test\Unit\Application\Event;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Event\QueuedEventDispatcher;
use Profesia\DddBackbone\Application\Messaging\MessageFactory;
use Profesia\DddBackbone\Test\NullEvent;
use Profesia\MessagingCore\Broking\Dto\BrokingBatchResponse;
use Profesia\MessagingCore\Broking\Dto\Message;
use Profesia\MessagingCore\Broking\Dto\MessageCollection;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;

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
}
