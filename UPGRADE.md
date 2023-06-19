# Upgrading guide
## Table of contents
* [From 1.x to 2.x](#how-to-upgrade-from-1x-to-2x)
## How to upgrade from 1.x to 2.x
2.0.0 is the new major version. The main purpose of this version is to introduce components for [Command Bus](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Application/Command/Bus/CommandBusInterface.php).
### BC Breaks
* Method `getEventMetadata` of the class [MetadataRegistry](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Application/Event/MetadataRegistry.php) now requires an instance of [AbstractDomainEvent](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Domain/Event/AbstractDomainEvent.php) as an argument instead of string representation of the event name.
* Method `registerEventMetadata` of the class [MetadataRegistry](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Application/Event/MetadataRegistry.php) now checks whether supplied class name exists and extends [AbstractDomainEvent](https://github.com/profesia/ddd-backbone/blob/v2.0.0/src/Domain/Event/AbstractDomainEvent.php).