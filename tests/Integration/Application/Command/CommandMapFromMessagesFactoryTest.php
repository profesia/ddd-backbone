<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Application\Command;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\Command\AbstractCommandFromMessage;
use Profesia\DddBackbone\Application\Command\Exception\CommandAlreadyRegisteredException;
use Profesia\DddBackbone\Application\Command\Exception\CommandClassDoesNotExistException;
use Profesia\DddBackbone\Application\Command\Exception\NoCommandRegisteredForEventTypeException;
use Profesia\DddBackbone\Application\Command\Exception\NotValidCommandClassException;
use Profesia\DddBackbone\Application\Command\Factory\CommandMapFromMessagesFactory;
use Profesia\DddBackbone\Test\Assets\NullCommand;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\DddBackbone\Test\Assets\NullOtherCommand;
use Profesia\MessagingCore\Broking\Dto\Receiving\PubSubReceivedMessage;

class CommandMapFromMessagesFactoryTest extends TestCase
{
    public function testCanDetectNonExistingCommandClass(): void
    {
        $factory = new CommandMapFromMessagesFactory();

        $className = 'testing';
        $this->expectExceptionObject(
            new CommandClassDoesNotExistException("Command class: [$className] does not exist")
        );
        $factory->registerCommandClass('test', $className);
    }

    public function testCanDetectedClassNotDerivedFromRequiredParentClass(): void
    {
        $factory = new CommandMapFromMessagesFactory();

        $className     = NullEvent::class;
        $abstractClass = AbstractCommandFromMessage::class;
        $this->expectExceptionObject(
            new NotValidCommandClassException("Command class: [$className] does not extend an [$abstractClass]")
        );
        $factory->registerCommandClass('test', $className);
    }

    public function testCanValidateCommandClass(): void
    {
        $factory = new CommandMapFromMessagesFactory();

        $factory->registerCommandClass('test', NullCommand::class);

        $this->assertTrue(true);
    }

    public function testCanDetectWildcardAlreadyRegistered(): void
    {
        $factory = new CommandMapFromMessagesFactory();

        $factory->registerCommandClass(CommandMapFromMessagesFactory::WILDCARD, NullCommand::class);

        $className = NullCommand::class;
        $this->expectExceptionObject(
            new CommandAlreadyRegisteredException("Wild card command class: [$className] already registered")
        );

        $factory->registerCommandClass(CommandMapFromMessagesFactory::WILDCARD, NullCommand::class);
    }

    public function testCanCreateWildcardCommand(): void
    {
        $factory = new CommandMapFromMessagesFactory();

        $factory->registerCommandClass(CommandMapFromMessagesFactory::WILDCARD, NullCommand::class);

        $data     = base64_encode(json_encode(['test' => true]));
        $instance = $factory->createFromReceivedMessage(
            PubSubReceivedMessage::createFromJsonString("{\"message\":{\"attributes\":{\"eventType\": \"testEventType\"}, \"data\":\"$data\"}}")
        );

        $this->assertInstanceOf(NullCommand::class, $instance);
    }

    public function testCanDetectNonExistingMapForSubscribeName(): void
    {
        $factory = new CommandMapFromMessagesFactory();

        $factory->registerCommandClass('subscribeName1', NullCommand::class);
        $factory->registerCommandClass('subscribeName2', NullOtherCommand::class);


        $eventType     = 'testEventType';
        $subscribeName = 'subscribeName';
        $this->expectExceptionObject(new NoCommandRegisteredForEventTypeException("No command registered for the subscribe name: [$subscribeName]"));
        $data = base64_encode(json_encode(['test' => true]));
        $factory->createFromReceivedMessage(
            PubSubReceivedMessage::createFromJsonString("{\"message\":{\"attributes\":{\"eventType\": \"$eventType\", \"subscribeName\": \"$subscribeName\"}, \"data\":\"$data\"}}")
        );
    }

    public function testCanCreate(): void
    {
        $factory = new CommandMapFromMessagesFactory();

        $factory->registerCommandClass('subscribeName1', NullCommand::class);
        $factory->registerCommandClass('subscribeName2', NullOtherCommand::class);

        $eventType     = 'eventType1';
        $subscribeName = 'subscribeName1';
        $data          = base64_encode(json_encode(['test' => true]));
        $instance      = $factory->createFromReceivedMessage(
            PubSubReceivedMessage::createFromJsonString("{\"message\":{\"attributes\":{\"eventType\": \"$eventType\", \"subscribeName\": \"$subscribeName\"}, \"data\":\"$data\"}}")
        );
        $this->assertInstanceOf(NullCommand::class, $instance);

        $eventType     = 'eventType2';
        $subscribeName = 'subscribeName2';
        $data          = base64_encode(json_encode(['test' => true]));
        $instance      = $factory->createFromReceivedMessage(
            PubSubReceivedMessage::createFromJsonString("{\"message\":{\"attributes\":{\"eventType\": \"$eventType\", \"subscribeName\": \"$subscribeName\"}, \"data\":\"$data\"}}")
        );

        $this->assertInstanceOf(NullOtherCommand::class, $instance);
    }
}