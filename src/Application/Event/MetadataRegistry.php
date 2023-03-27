<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\BadMetadataKeyException;
use Profesia\DddBackbone\Application\Event\Exception\MissingEventMetadataException;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;

final class MetadataRegistry
{
    /** @var array<string, EventMetadata> */
    private array $config = [];

    public static function createFromArrayConfig(array $events, string $provider, string $target): self
    {
        $instance = new self();
        foreach ($events as $eventName => $eventConfig) {
            $configToUse = [
                'resource' => $eventConfig['resource'],
                'provider' => $provider,
            ];

            $configToUse['target'] = $eventConfig['targetOverride'] ?? $target;

            $instance->registerEventMetadata(
                $eventName,
                EventMetadata::createFromArray($configToUse)
            );
        }

        return $instance;
    }

    public function registerEventMetadata(string $eventName, EventMetadata $metadata): void
    {
        if (class_exists($eventName) !== false) {
            throw new BadMetadataKeyException("Supplied string: [{$eventName}] is not a loadable class");
        }

        if (is_subclass_of($eventName, AbstractDomainEvent::class) === false) {
            throw new BadMetadataKeyException("Class: [{$eventName}] is not a descendant of AbstractDomainEvent class");
        }

        $this->config[$eventName] = $metadata;
    }

    public function getEventMetadata(AbstractDomainEvent $event): EventMetadata
    {
        $eventName = $event::getEventName();
        if ($this->hasEventMetadata($event)) {
            throw new MissingEventMetadataException("Metadata for event: [{$eventName}] are not registered");
        }

        return $this->config[$eventName];
    }

    public function hasEventMetadata(AbstractDomainEvent $event): bool
    {
        return array_key_exists($event::getEventName(), $this->config);
    }
}
