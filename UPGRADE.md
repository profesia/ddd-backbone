# Upgrading guide
## Table of contents
* [From 5.x to 6.x](#how-to-upgrade-from-5x-to-6x)
* [From 4.x to 5.x](#how-to-upgrade-from-4x-to-5x)
* [From 3.x to 4.x](#how-to-upgrade-from-3x-to-4x)
* [From 2.x to 3.x](#how-to-upgrade-from-2x-to-3x)
* [From 1.x to 2.x](#how-to-upgrade-from-1x-to-2x)

## How to upgrade from 5.x to 6.x
6.0.0 is the new major version. The main purpose is to improve exception handling in `TransactionService`.
### BC Breaks
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