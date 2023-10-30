<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Application\Event\Exception\BadEventMetadataConfigurationExceptionAbstract;

final class EventMetadata
{
    private string $resource;
    private string $provider;
    private string $publishingTopic;
    private string $errorTopic;

    private function __construct(string $resource, string $provider, string $publishingTopic, string $errorTopic)
    {
        $this->resource        = $resource;
        $this->provider        = $provider;
        $this->publishingTopic = $publishingTopic;
        $this->errorTopic      = $errorTopic;
    }

    public static function createFromArray(array $config): self
    {
        $requiredKeys = [
            'resource',
            'provider',
            'publishingTopic',
            'errorTopic'
        ];

        foreach ($requiredKeys as $keyToCheck) {
            if (array_key_exists($keyToCheck, $config) === false) {
                throw new BadEventMetadataConfigurationExceptionAbstract("Key: [{$keyToCheck}] is missing in the supplied config");
            }
        }

        return new self(
            $config['resource'],
            $config['provider'],
            $config['publishingTopic'],
            $config['errorTopic']
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

    public function getPublishingTopic(): string
    {
        return $this->publishingTopic;
    }

    public function getErrorTopic(): string
    {
        return $this->errorTopic;
    }
}
