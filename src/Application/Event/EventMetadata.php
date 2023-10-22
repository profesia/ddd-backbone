<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\BadEventMetadataConfigurationExceptionAbstract;

final class EventMetadata
{
    private string $resource;
    private string $provider;
    private string $target;
    private bool   $isPublic;

    private function __construct(string $resource, string $provider, string $target, bool $isPublic = true)
    {
        $this->resource = $resource;
        $this->provider = $provider;
        $this->target   = $target;
        $this->isPublic = $isPublic;
    }

    public static function createFromArray(array $config): self
    {
        $requiredKeys = [
            'resource',
            'provider',
            'target',
        ];

        foreach ($requiredKeys as $keyToCheck) {
            if (array_key_exists($keyToCheck, $config) === false) {
                throw new BadEventMetadataConfigurationExceptionAbstract("Key: [{$keyToCheck}] is missing in the supplied config");
            }
        }

        if (array_key_exists('isPublic', $config) === false) {
            $config['isPublic'] = true;
        }

        return new self(
            $config['resource'],
            $config['provider'],
            $config['target'],
            $config['isPublic']
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

    public function isPublic(): bool
    {
        return $this->isPublic;
    }
}
