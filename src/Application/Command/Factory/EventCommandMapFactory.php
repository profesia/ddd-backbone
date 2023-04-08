<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

final class EventCommandMapFactory implements CommandFactoryInterface
{
    /** @var string[] */
    private array $eventCommandMap = [];

    /**
     * @inheritdoc
     */
    public function registerCommandClass(string $eventType, string $commandClass): self
    {
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
            throw new NoCommandRegisteredForEventTypeException("No command registered for event type: [{$eventType}]");
        }

        if (class_exists($this->eventCommandMap[$eventType]) === false) {
            throw new CommandClassDoesNotExistException("Command class: [{$this->eventCommandMap[$eventType]}] does not exist");
        }

        return call_user_func(
            [
                $this->eventCommandMap[$eventType],
                'createFromJsonString'
            ],
            $receivedMessage->decodePayload()
        );
    }
}