<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\PubSub\Event;

use Profesia\DddBackbone\Application\Event\DequeueDispatcherInterface;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\DddBackbone\Domain\Event\DispatcherInterface;
use Profesia\MessagingCore\Broking\Dto\Message;
use Profesia\MessagingCore\Broking\Dto\MessageCollection;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;

final class QueuedEventDispatcher implements DispatcherInterface, DequeueDispatcherInterface
{
    /** @var AbstractDomainEvent[]  */
    private array $events;

    public function __construct(
        private MessageBrokerInterface $messageBroker,
        private string $channel,
        private string $correlationId
    ) {}

    public function dispatch(AbstractDomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function flush(): void
    {
        $messages = array_map(
            function (AbstractDomainEvent $event): Message {
                return new Message(
                    'resource',
                    get_class($event),
                    'provider',
                    'objectId',
                    $event->getOccurredOn(),
                    $this->correlationId,
                    'target',
                    $event->getPayload()
                );
            },
            $this->events
        );

        $brokingResponse = $this->messageBroker->publish(
            MessageCollection::createFromMessagesAndChannel(
                $this->channel,
                ...$messages
            )
        );
    }

}
