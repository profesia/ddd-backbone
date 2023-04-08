<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

interface CommandFactoryInterface
{
    /**
     * @param string $eventType
     * @param string $commandClass
     * @return $this
     */
    public function registerCommandClass(string $eventType, string $commandClass): self;

    /**
     * @param ReceivedMessage $receivedMessage
     * @return CommandInterface
     *
     * @throws NoCommandRegisteredForEventTypeException
     * @throws CommandClassDoesNotExistException
     */
    public function createFromReceivedMessage(ReceivedMessage $receivedMessage): CommandInterface;
}