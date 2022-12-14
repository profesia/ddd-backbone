<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Messaging\MessageFactory;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCore\Broking\Dto\Message;
use Profesia\MessagingCore\Broking\Dto\MessageCollection;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;

final class QueuedEventDispatcher implements DequeueDispatcherInterface
{
    /** @var AbstractDomainEvent[] */
    private array $events;

    public function __construct(
        private MessageBrokerInterface $messageBroker,
        private MessageFactory $messageFactory,
        private string $channel,
        private string $correlationId
    ) {
    }

    public function dispatch(AbstractDomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function flush(): void
    {
        $messages = array_map(
            function (AbstractDomainEvent $event): Message {
                return $this->messageFactory->createFromDomainEvent(
                    $event,
                    $this->correlationId
                );
            },
            $this->events
        );

        $this->messageBroker->publish(
            MessageCollection::createFromMessagesAndChannel(
                   $this->channel,
                ...$messages
            )
        );
    }

}
