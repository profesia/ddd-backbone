<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Messaging;

use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCore\Broking\Dto\Sending\AwsMessage;
use Profesia\MessagingCore\Broking\Dto\Sending\MessageInterface;

class AwsMessageFactory implements MessageFactoryInterface
{
    private MetadataRegistry $metadataRegistry;

    public function __construct(
        MetadataRegistry $metadataRegistry
    ) {
        $this->metadataRegistry = $metadataRegistry;
    }

    public function createFromDomainEvent(AbstractDomainEvent $event, string $correlationId): MessageInterface
    {
        $metadata      = $this->metadataRegistry->getEventMetadata($event);
        $subscribeName = "{$metadata->getProvider()}.{$event->getPublicName()}";

        return new AwsMessage(
            $metadata->getResource(),
            get_class($event),
            $metadata->getProvider(),
            $event->getPrimaryId(),
            $event->getOccurredOn(),
            $correlationId,
            $subscribeName,
            $metadata->getTopic(),
            $event->getPayload(),
        );
    }
}
