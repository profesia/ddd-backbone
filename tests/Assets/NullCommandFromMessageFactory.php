<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Factory\AbstractCommandFromMessageFactory;
use Profesia\DddBackbone\Application\Command\Factory\CommandFromMessageFactoryInterface;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessageInterface;

class NullCommandFromMessageFactory extends AbstractCommandFromMessageFactory
{
    private ?string $commandClass = null;

    public function registerCommandClass(string $eventType, string $commandClass): CommandFromMessageFactoryInterface
    {
        self::validateCommandClass($commandClass);
        $this->commandClass = $commandClass;

        return $this;
    }

    public function createFromReceivedMessage(ReceivedMessageInterface $receivedMessage): CommandInterface
    {
        return self::createCommand(
            $this->commandClass,
            $receivedMessage
        );
    }
}