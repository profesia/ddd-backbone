# Upgrading guide
## Table of contents
* [From 5.x to 6.x](#how-to-upgrade-from-5x-to-6x)
* [From 4.x to 5.x](#how-to-upgrade-from-4x-to-5x)
* [From 3.x to 4.x](#how-to-upgrade-from-3x-to-4x)
* [From 2.x to 3.x](#how-to-upgrade-from-2x-to-3x)
* [From 1.x to 2.x](#how-to-upgrade-from-1x-to-2x)

## How to upgrade from 5.x to 6.x
6.0.0 is the new major version. The main purpose of this major release is:
* to extend the [CommandBusInterface](https://github.com/profesia/ddd-backbone/blob/master/src/Application/Command/Bus/CommandBusInterface.php) with a synchronous dispatch method that can return a value.
* to improve exception handling in `TransactionService`.
* to replace the heavy `profesia/messaging-core` runtime dependency with the lightweight `profesia/messaging-core-contracts`, so the library no longer pulls in the AWS and Google Cloud SDKs.
### BC Breaks
* Method `dispatchSync` has been added to [CommandBusInterface](https://github.com/profesia/ddd-backbone/blob/master/src/Application/Command/Bus/CommandBusInterface.php). All classes implementing this interface must add a `dispatchSync(CommandInterface $command): mixed` method.
* The runtime dependency changed from `profesia/messaging-core` to `profesia/messaging-core-contracts`. All type hints against messaging classes moved from the `Profesia\MessagingCore\` namespace to `Profesia\MessagingCoreContracts\` (e.g. `MessageBrokerInterface`, `MessageInterface`, `GroupedMessagesCollection`, `ReceivedMessageInterface`). The broker implementation you inject must implement the contracts interface.
* The minimum PHP version was raised to `8.2` (required by `profesia/messaging-core-contracts`).
* The deprecated `Profesia\DddBackbone\Application\Messaging\MessageFactory` class was removed.
* The provider-specific factories `AwsMessageFactory` and `PubSubMessageFactory` were replaced by a single `Profesia\DddBackbone\Application\Messaging\DomainEventMessageFactory`. Its constructor now takes the `MetadataRegistry` **and** a `Profesia\MessagingCoreContracts\Broking\Dto\Sending\Factory\MessageFactoryInterface`. Inject the AWS or PubSub message factory from `profesia/messaging-core` to choose the concrete message type instead of picking a factory class here.
* `TransactionService::transactional()` now wraps all thrown exceptions (from the callable and from commit) in `TransactionServiceException` before re-throwing. Code that previously caught the original exception type directly must now catch `TransactionServiceException` (or inspect `$exception->getPrevious()` to access the original exception).
* Added try-catch statement for rollback operation since it can also throw an exception. In this case the exception causing the need for rollback is set as previous to the current exception to preserve the stack trace.
## How to upgrade from 4.x to 5.x
5.0.0 is the new major version. The main purpose is the change contract according to the [messaging core](https://github.com/profesia/messaging-core) v4.
## How to upgrade from 3.x to 4.x
4.0.0 is the new major version. The main purpose of this major release is to change the inner working of event dispatcher to be able to dispatch events based on their topic settings.
## How to upgrade from 2.x to 3.x
3.0.0 is the new major version. The main purpose is to introduce `Public Name` for [AbstractDomainEvent](https://github.com/profesia/ddd-backbone/blob/v3.0.0/src/Domain/Event/AbstractDomainEvent.php).
### BC Breaks
* Introduced abstract method `getPublicName`, that requires an implementation in classes implementing this interface.
## How to upgrade from 1.x to 2.x
2.0.0 is the new major version. The main purpose of this version is to introduce components for [Command Bus](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Application/Command/Bus/CommandBusInterface.php).
### BC Breaks
* Method `getEventMetadata` of the class [MetadataRegistry](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Application/Event/MetadataRegistry.php) now requires an instance of [AbstractDomainEvent](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Domain/Event/AbstractDomainEvent.php) as an argument instead of string representation of the event name.
* Method `registerEventMetadata` of the class [MetadataRegistry](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Application/Event/MetadataRegistry.php) now checks wheAbstractDOnaither supplied class name exists and extends [AbstractDomainEvent](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Domain/Event/AbstractDomainEvent.php).