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
    private MessageBrokerInterface $broker;
    private MessageFactory $messageFactory;
    private string $channel;
    private string $correlationId;

    /** @var AbstractDomainEvent[] */
    private array $events = [];
    private int $batchSize;

    public function __construct(
        MessageBrokerInterface $broker,
        MessageFactory $messageFactory,
        string $channel,
        string $correlationId,
        int $batchSize = 500
    ) {
        if ($batchSize <= 0 || $batchSize > 1000) {
            throw new RuntimeException('Batch size should be in the interval <1,1000>');
        }

        $this->broker         = $broker;
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
        $counterByTopic       = [];
        $messagesBatchByTopic = [];
        foreach ($this->events as $event) {
            $message = $this->messageFactory->createFromDomainEvent(
                $event,
                $this->correlationId
            );

            $topic = $message->getTopic() !== null ? $message->getTopic() : $this->channel;

            array_key_exists($topic, $counterByTopic) ? $counterByTopic[$topic]++ : $counterByTopic[$topic] = 1;

            if (array_key_exists($topic, $messagesBatchByTopic) === false) {
                $messagesBatchByTopic[$topic] = [$message];
            } else {
                $messagesBatchByTopic[$topic][] = $message;
            }

            if ($counterByTopic[$topic] % $this->batchSize === 0) {
                $counterByTopic[$topic] = 0;

                $this->broker->publish(
                    MessageCollection::createFromMessagesAndChannel(
                        $topic,
                        ...$messagesBatchByTopic[$topic]
                    )
                );

                $messagesBatchByTopic[$topic] = [];
            }
        }

        foreach (array_keys($messagesBatchByTopic) as $topic) {
            if ($messagesBatchByTopic[$topic] !== []) {
                $this->broker->publish(
                    MessageCollection::createFromMessagesAndChannel(
                        $topic,
                        ...$messagesBatchByTopic[$topic]
                    )
                );
            }
        }
    }
}
