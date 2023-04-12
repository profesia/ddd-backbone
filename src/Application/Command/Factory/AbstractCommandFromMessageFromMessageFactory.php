<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\AbstractCommandFromMessage;
use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NotValidCommandClassException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

abstract class AbstractCommandFromMessageFromMessageFactory implements CommandFromMessageFactoryInterface
{
    /**
     * @param string $className
     * @return bool
     * @throws NotValidCommandClassException
     */
    protected static function validateCommandClass(string $className): bool
    {
        if (class_exists($className) === false) {
            throw new CommandClassDoesNotExistException("Command class: [$className] does not exist");

        }

        $abstractClass = AbstractCommandFromMessage::class;
        if (is_subclass_of($className, $abstractClass) === false) {
            throw new NotValidCommandClassException("Command class: [$className] does not extend an [$abstractClass]");
        }

        return true;
    }

    protected static function createCommand(string $commandClass, ReceivedMessage $message): CommandInterface
    {
        return $commandClass($message->getDecodedMessage());
    }
}