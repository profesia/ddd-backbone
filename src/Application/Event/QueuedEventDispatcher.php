<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\MessagingCore\Broking\Dto\GroupedMessagesCollection;
use RuntimeException;
use Profesia\DddBackbone\Application\Messaging\MessageFactory;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;

final class QueuedEventDispatcher implements DequeueDispatcherInterface
{
    private MessageBrokerInterface $messageBroker;
    private MessageFactory $messageFactory;
    private string $correlationId;

    /** @var AbstractDomainEvent[] */
    private array $events = [];
    private int $batchSize;

    public function __construct(
        MessageBrokerInterface $messageBroker,
        MessageFactory $messageFactory,
        string $correlationId,
        int $batchSize = 500
    ) {
        if ($batchSize <= 0 || $batchSize > 1000) {
            throw new RuntimeException('Batch size should be in the interval <1,1000>');
        }

        $this->messageBroker  = $messageBroker;
        $this->messageFactory = $messageFactory;
        $this->correlationId  = $correlationId;
        $this->batchSize      = $batchSize;
    }

    public function dispatch(AbstractDomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function flush(): void
    {
        $counter       = 1;
        $messagesBatch = [];
        foreach ($this->events as $event) {
            $messagesBatch[] = $this->messageFactory->createFromDomainEvent(
                $event,
                $this->correlationId
            );

            if ($counter % $this->batchSize === 0) {
                $counter = 0;

                $this->messageBroker->publish(
                    GroupedMessagesCollection::createFromMessages(
                        ...$messagesBatch
                    )
                );

                $messagesBatch = [];
            }

            $counter++;
        }

        if ($messagesBatch !== []) {
            $this->messageBroker->publish(
                GroupedMessagesCollection::createFromMessages(
                    ...$messagesBatch
                )
            );
        }
    }

    public function clear(): void
    {
        $this->events = [];
    }
}
