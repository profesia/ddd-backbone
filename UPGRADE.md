# Upgrading guide
## Table of contents
* [From 4.x to 5.x](#how-to-upgrade-from-4x-to-5x)
* [From 3.x to 4.x](#how-to-upgrade-from-3x-to-4x)
* [From 2.x to 3.x](#how-to-upgrade-from-2x-to-3x)
* [From 1.x to 2.x](#how-to-upgrade-from-1x-to-2x)

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