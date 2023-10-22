<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Messaging\MessageFactory;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCore\Broking\Dto\MessageCollection;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;
use RuntimeException;

final class QueuedSplittingEventDispatcher implements DequeueDispatcherInterface
{
    private MessageBrokerInterface $publicBroker;
    private MessageBrokerInterface $privateBroker;
    private MessageFactory         $messageFactory;
    private string                 $channel;
    private string                 $correlationId;

    /** @var AbstractDomainEvent[] */
    private array $events = [];
    private int   $batchSize;

    public function __construct(
        MessageBrokerInterface $publicBroker,
        MessageBrokerInterface $privateBroker,
        MessageFactory $messageFactory,
        string $channel,
        string $correlationId,
        int $batchSize = 500
    )
    {
        if ($batchSize <= 0 || $batchSize > 1000) {
            throw new RuntimeException('Batch size should be in the interval <1,1000>');
        }

        $this->publicBroker   = $publicBroker;
        $this->privateBroker  = $privateBroker;
        $this->messageFactory = $messageFactory;
        $this->channel        = $channel;
        $this->correlationId  = $correlationId;
        $this->batchSize      = $batchSize;
    }

    public function dispatch(AbstractDomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function flush(): void
    {
        $counter              = 1;
        $publicMessagesBatch  = [];
        $privateMessagesBatch = [];
        foreach ($this->events as $event) {
            $message = $this->messageFactory->createFromDomainEvent(
                $event,
                $this->correlationId
            );
            if ($message->isPublic() === true) {
                $publicMessagesBatch[] = $message;
            } else {
                $privateMessagesBatch[] = $message;
            }

            if ($counter % $this->batchSize === 0) {
                $counter = 0;

                $this->publicBroker->publish(
                    MessageCollection::createFromMessagesAndChannel(
                        $this->channel,
                        ...$publicMessagesBatch
                    )
                );
                $this->privateBroker->publish(
                    MessageCollection::createFromMessagesAndChannel(
                        $this->channel,
                        ...$privateMessagesBatch
                    )
                );

                $publicMessagesBatch  = [];
                $privateMessagesBatch = [];
            }

            $counter++;
        }

        if ($publicMessagesBatch !== []) {
            $this->publicBroker->publish(
                MessageCollection::createFromMessagesAndChannel(
                    $this->channel,
                    ...$publicMessagesBatch
                )
            );
        }

        if ($privateMessagesBatch !== []) {
            $this->privateBroker->publish(
                MessageCollection::createFromMessagesAndChannel(
                    $this->channel,
                    ...$privateMessagesBatch
                )
            );
        }
    }
}