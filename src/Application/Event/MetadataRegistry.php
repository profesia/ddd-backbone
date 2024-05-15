<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\BadMetadataKeyException;
use Profesia\DddBackbone\Application\Event\Exception\MissingEventMetadataException;
use Profesia\DddBackbone\Domain\Event\AbstractDomainEvent;

class MetadataRegistry
{
    /** @var array<string, EventMetadata> */
    private array $config = [];

    /**
     * @param array<string, array<string, string>> $events
     * @param string $provider
     * @return self
     */
    public static function createFromArrayConfig(array $events, string $provider): self
    {
        $instance = new self();
        foreach ($events as $eventName => $eventConfig) {
            $configToUse = [
                'resource' => $eventConfig['resource'],
                'provider' => $eventConfig['provider'] ?? $provider,
                'topic'    => $eventConfig['topic'],
            ];

            $instance->registerEventMetadata(
                $eventName,
                EventMetadata::createFromArray($configToUse)
            );
        }

        return $instance;
    }

    public function registerEventMetadata(string $eventName, EventMetadata $metadata): void
    {
        if (class_exists($eventName) === false) {
            throw new BadMetadataKeyException("Supplied string: [$eventName] is not a loadable class");
        }

        $parentClass = AbstractDomainEvent::class;
        if (is_subclass_of($eventName, $parentClass) === false) {
            throw new BadMetadataKeyException("Class: [$eventName] is not a descendant of [$parentClass] class");
        }

        $this->config[call_user_func([$eventName, 'getEventName'])] = $metadata;
    }

    public function getEventMetadata(AbstractDomainEvent $event): EventMetadata
    {
        $eventName = $event::getEventName();
        if ($this->hasEventMetadata($event) === false) {
            throw new MissingEventMetadataException("Metadata for event: [{$eventName}] are not registered");
        }

        return $this->config[$eventName];
    }

    public function hasEventMetadata(AbstractDomainEvent $event): bool
    {
        return array_key_exists($event::getEventName(), $this->config);
    }
}
