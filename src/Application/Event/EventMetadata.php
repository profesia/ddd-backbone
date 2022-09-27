<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\BadEventMetadataConfigurationExceptionAbstract;

final class EventMetadata
{
    private function __construct(
        private string $resource,
        private string $provider,
        private string $target
    )
    {
    }

    public static function createFromArray(array $config): self
    {
        if (array_key_exists('resource', $config) === false) {
            throw new BadEventMetadataConfigurationExceptionAbstract('Key: [resource] is missing in the supplied config');
        }

        if (array_key_exists('provider', $config) === false) {
            throw new BadEventMetadataConfigurationExceptionAbstract('Key: [provider] is missing in the supplied config');
        }

        if (array_key_exists('target', $config) === false) {
            throw new BadEventMetadataConfigurationExceptionAbstract('Key: [target] is missing in the supplied config');
        }

        return new self(
            $config['resource'],
            $config['provider'],
            $config['target']
        );
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
