<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\Messaging\MessageFactoryInterface;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCoreContracts\Broking\Dto\Sending\MessageInterface;

final class NullMessageFactory implements MessageFactoryInterface
{
    public function createFromDomainEvent(AbstractDomainEvent $event, string $correlationId): MessageInterface
    {
        return new NullMessage($event->getPublicName());
    }
}
