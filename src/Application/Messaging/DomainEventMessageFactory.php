<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Messaging;

use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCoreContracts\Broking\Dto\Sending\Factory\MessageFactoryInterface as MessageDtoFactoryInterface;
use Profesia\MessagingCoreContracts\Broking\Dto\Sending\MessageInterface;

final class DomainEventMessageFactory implements MessageFactoryInterface
{
    public function __construct(
        private readonly MetadataRegistry $metadataRegistry,
        private readonly MessageDtoFactoryInterface $messageFactory,
    ) {
    }

    public function createFromDomainEvent(AbstractDomainEvent $event, string $correlationId): MessageInterface
    {
        $metadata      = $this->metadataRegistry->getEventMetadata($event);
        $subscribeName = "{$metadata->getProvider()}.{$event->getPublicName()}";

        return $this->messageFactory->create(
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
