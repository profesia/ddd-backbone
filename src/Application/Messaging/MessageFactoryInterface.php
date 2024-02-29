<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Messaging;

use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;
use Profesia\MessagingCore\Broking\Dto\Sending\MessageInterface;

interface MessageFactoryInterface
{
    public function createFromDomainEvent(AbstractDomainEvent $event, string $correlationId): MessageInterface;
}
