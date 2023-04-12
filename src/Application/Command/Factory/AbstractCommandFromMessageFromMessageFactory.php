<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\AbstractCommandFromMessage;
use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NotValidCommandClassException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;
use ReflectionClass;
use ReflectionException;

abstract class AbstractCommandFromMessageFromMessageFactory implements CommandFromMessageFactoryInterface
{
    /**
     * @param string $className
     * @return bool
     * @throws NotValidCommandClassException
     */
    protected static function validateCommandClass(string $className): bool
    {
        try {
            $reflectionClass = new ReflectionClass($className);
            $abstractClass = AbstractCommandFromMessage::class;
            if ($reflectionClass->isInstance($abstractClass) === false) {
                throw new NotValidCommandClassException("Command class: [$className] does not extend an [$abstractClass]");
            }

            return true;
        } catch (ReflectionException $e) {
            throw new CommandClassDoesNotExistException("Not valid command class supplied. Details: [{$e->getMessage()}]");
        }
    }

    protected static function createCommand(string $commandClass, ReceivedMessage $message): CommandInterface
    {
        return $commandClass($message->getDecodedMessage());
    }
}