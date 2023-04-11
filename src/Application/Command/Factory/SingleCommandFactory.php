<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

final class SingleCommandFactory extends AbstractCommandFactory
{
    private ?string $commandClass = null;

    /**
     * @inheritdoc
     */
    public function registerCommandClass(string $eventType, string $commandClass): CommandFactoryInterface
    {
        self::validateCommandClass($commandClass);
        $this->commandClass = $commandClass;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createFromReceivedMessage(ReceivedMessage $receivedMessage): CommandInterface
    {
        $eventType = $receivedMessage->getEventType();
        if ($this->commandClass === null) {
            throw new NoCommandRegisteredForEventTypeException("No command registered for the event type: [$eventType]");
        }

        return self::createCommand($this->commandClass, $receivedMessage);
    }

}