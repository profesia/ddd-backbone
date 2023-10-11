<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessageInterface;

interface CommandFromMessageFactoryInterface
{
    /**
     * @param string $subscribeName
     * @param string $commandClass
     * @return $this
     */
    public function registerCommandClass(string $subscribeName, string $commandClass): self;

    /**
     * @param ReceivedMessageInterface $receivedMessage
     * @return CommandInterface
     *
     * @throws NoCommandRegisteredForEventTypeException
     * @throws CommandClassDoesNotExistException
     */
    public function createFromReceivedMessage(ReceivedMessageInterface $receivedMessage): CommandInterface;
}