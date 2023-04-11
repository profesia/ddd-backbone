<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Factory;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NotValidCommandClassException;
use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;
use ReflectionClass;
use ReflectionException;

abstract class AbstractCommandFactory implements CommandFactoryInterface
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
            if ($reflectionClass->implementsInterface(CommandInterface::class) === false) {
                $interface = CommandInterface::class;
                throw new NotValidCommandClassException("Command class: [$className] does not implement a [$interface] interface");
            }

            return true;
        } catch (ReflectionException $e) {
            throw new CommandClassDoesNotExistException("Not valid command class supplied. Details: [{$e->getMessage()}]");
        }
    }

    protected static function createCommand(string $commandClass, ReceivedMessage $message): CommandInterface
    {
        return call_user_func(
            [
                $commandClass,
                'createFromReceivedMessage'
            ],
            $message
        );
    }
}