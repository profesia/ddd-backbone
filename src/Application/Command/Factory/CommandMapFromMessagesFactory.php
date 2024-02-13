<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\AbstractCommandFromMessage;
use Profesia\DddBackbone\Application\Command\Exception\CommandAlreadyRegisteredException;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\DddBackbone\Application\Command\Exception\NotValidCommandClassException;
use Profesia\MessagingCore\Broking\Dto\Receiving\ReceivedMessageInterface;

final class CommandMapFromMessagesFactory implements CommandFromMessageFactoryInterface
{
    const WILDCARD = '*';

    /** @var string[] */
    private array $eventCommandMap = [];

    /**
     * @inheritdoc
     */
    public function registerCommandClass(string $subscribeName, string $commandClass): self
    {
        if (array_key_exists(self::WILDCARD, $this->eventCommandMap) === true) {
            $registeredClass = $this->eventCommandMap[self::WILDCARD];
            throw new CommandAlreadyRegisteredException("Wild card command class: [$registeredClass] already registered");
        }

        self::validateCommandClass($commandClass);
        $this->eventCommandMap[$subscribeName] = $commandClass;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createFromReceivedMessage(ReceivedMessageInterface $receivedMessage): AbstractCommandFromMessage
    {
        $hasWildcard = array_key_exists(self::WILDCARD, $this->eventCommandMap);

        if ($hasWildcard === true) {
            return self::createCommand($this->eventCommandMap[self::WILDCARD], $receivedMessage);
        }

        $subscribeName           = $receivedMessage->getSubscribeName();
        $isCommandRegistered = array_key_exists($subscribeName, $this->eventCommandMap);
        if ($isCommandRegistered === false) {
            throw new NoCommandRegisteredForEventTypeException("No command registered for the subscribe name: [$subscribeName]");
        }

        return self::createCommand($this->eventCommandMap[$subscribeName], $receivedMessage);
    }

    /**
     * @param string $className
     * @throws NotValidCommandClassException
     * @throws CommandClassDoesNotExistException
     */
    private static function validateCommandClass(string $className): void
    {
        if (class_exists($className) === false) {
            throw new CommandClassDoesNotExistException("Command class: [$className] does not exist");
        }

        $abstractClass = AbstractCommandFromMessage::class;
        if (is_subclass_of($className, $abstractClass) === false) {
            throw new NotValidCommandClassException("Command class: [$className] does not extend an [$abstractClass]");
        }
    }

    private static function createCommand(string $commandClass, ReceivedMessageInterface $message): AbstractCommandFromMessage
    {
        /**
         * @phpstan-ignore-next-line
         */
        return new $commandClass($message);
    }
}