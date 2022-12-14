<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Messaging;

use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCore\Broking\Dto\Message;

class MessageFactory
{
    public function __construct(
        private MetadataRegistry $metadataRegistry
    )
    {}

    public function createFromDomainEvent(AbstractDomainEvent $event, string $correlationId): Message
    {
        $metadata = $this->metadataRegistry->getEventMetadata(
            get_class($event)
        );

        return new Message(
            $metadata->getResource(),
            get_class($event),
            $metadata->getProvider(),
            $event->getPrimaryId(),
            $event->getOccurredOn(),
            $correlationId,
            $metadata->getTarget(),
            $event->getPayload()
        );
    }
}
