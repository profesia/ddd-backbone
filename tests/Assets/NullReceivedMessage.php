<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\MessagingCoreContracts\Broking\Dto\Receiving\ReceivedMessageInterface;

final class NullReceivedMessage implements ReceivedMessageInterface
{
    /**
     * @param array<string, mixed> $decodedMessage
     */
    public function __construct(
        private readonly string $eventType,
        private readonly string $subscribeName,
        private readonly array $decodedMessage = [],
    ) {
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getSubscribeName(): string
    {
        return $this->subscribeName;
    }

    public function getDecodedMessage(): array
    {
        return $this->decodedMessage;
    }
}
