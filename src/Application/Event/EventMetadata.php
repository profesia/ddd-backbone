<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\BadEventMetadataConfigurationExceptionAbstract;

final class EventMetadata
{
    private string $resource;
    private string $provider;
    private string $topic;

    private function __construct(string $resource, string $provider, string $topic)
    {
        $this->resource = $resource;
        $this->provider = $provider;
        $this->topic    = $topic;
    }

    public static function createFromArray(array $config): self
    {
        $requiredKeys = [
            'resource',
            'provider',
            'topic',
        ];

        foreach ($requiredKeys as $keyToCheck) {
            if (array_key_exists($keyToCheck, $config) === false) {
                throw new BadEventMetadataConfigurationExceptionAbstract("Key: [{$keyToCheck}] is missing in the supplied config");
            }
        }

        return new self(
            $config['resource'],
            $config['provider'],
            $config['topic']
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

    public function getTopic(): string
    {
        return $this->topic;
    }
}
