<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\MissingEventMetadataException;

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
