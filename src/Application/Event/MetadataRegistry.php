<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\MissingEventMetadataException;

class MetadataRegistry
{
    /** @var array<string, EventMetadata>  */
    private array $config;

    public static function createFromArrayConfig(array $config): self
    {
        $instance = new self();
        foreach ($config as $eventName => $eventConfig) {
            $instance->registerEventMetadata(
                $eventName,
                EventMetadata::createFromArray($eventConfig)
            );
        }

        return $instance;
    }

    public function registerEventMetadata(string $eventName, EventMetadata $metadata): void
    {
        $this->config[$eventName] = $metadata;
    }

    public function getEventMetadata(string $eventName): EventMetadata
    {
        if (array_key_exists($eventName, $this->config) === false) {
            throw new MissingEventMetadataException("Metadata for event: [{$eventName}] are not registered");
        }

        return $this->config[$eventName];
    }
}
