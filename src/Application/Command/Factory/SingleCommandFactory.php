<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

class SingleCommandFactory implements CommandFactoryInterface
{
    private ?string $commandClass = null;

    public function registerCommandClass(string $eventType, string $commandClass): CommandFactoryInterface
    {
        $this->commandClass = $commandClass;

        return $this;
    }

    public function createFromReceivedMessage(ReceivedMessage $receivedMessage): CommandInterface
    {
        $eventType = $receivedMessage->getEventType();
        if ($this->commandClass === null) {
            throw new NoCommandRegisteredForEventTypeException("No command registered for event type: [{$eventType}]");
        }

        if (class_exists($this->commandClass) === false) {
            throw new CommandClassDoesNotExistException("Command class: [{$this->commandClass}] does not exist");
        }

        return call_user_func(
            [
                $this->commandClass,
                'createFromJsonString'
            ],
            $receivedMessage->decodePayload()
        );
    }

}