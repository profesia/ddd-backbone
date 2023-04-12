<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

final class CommandMapFromMessagesFactory extends AbstractCommandFromMessageFromMessageFactory
{
    /** @var string[] */
    private array $eventCommandMap = [];

    /**
     * @inheritdoc
     */
    public function registerCommandClass(string $eventType, string $commandClass): self
    {
        self::validateCommandClass($commandClass);
        $this->eventCommandMap[$eventType] = $commandClass;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createFromReceivedMessage(ReceivedMessage $receivedMessage): CommandInterface
    {
        $eventType = $receivedMessage->getEventType();
        if (array_key_exists($eventType, $this->eventCommandMap) === false) {
            throw new NoCommandRegisteredForEventTypeException("No command registered for the event type: [$eventType]");
        }

        return self::createCommand($this->eventCommandMap[$eventType], $receivedMessage);
    }
}