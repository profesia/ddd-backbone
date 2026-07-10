<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Application\Message;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Event\MetadataRegistry;
use Profesia\DddBackbone\Application\Messaging\DomainEventMessageFactory;
use Profesia\DddBackbone\Test\Assets\NullEvent;
use Profesia\DddBackbone\Test\Assets\NullMessage;
use Profesia\MessagingCoreContracts\Broking\Dto\Sending\Factory\MessageFactoryInterface;

class DomainEventMessageFactoryTest extends MockeryTestCase
{
    public function testDelegatesToInjectedFactoryWithMetadataFromEvent(): void
    {
        $globalProvider = 'globalProvider';
        $event          = new NullEvent('8d0e43fd-d5d4-4b61-8963-e777c591cf0d');
        $config         = [
            'resource' => 'resource',
            'topic'    => 'topic',
        ];

        $registry = MetadataRegistry::createFromArrayConfig(
            [
                $event::getEventName() => $config,
            ],
            $globalProvider
        );

        $expectedMessage = new NullMessage('topic');

        /** @var MessageFactoryInterface|MockInterface $messageDtoFactory */
        $messageDtoFactory = Mockery::mock(MessageFactoryInterface::class);
        $messageDtoFactory
            ->shouldReceive('create')
            ->once()
            ->withArgs(
                function (
                    string $resource,
                    string $eventType,
                    string $provider,
                    string $objectId,
                    DateTimeImmutable $eventOccurredOn,
                    string $correlationId,
                    string $subscribeName,
                    string $topic,
                    array $payload
                ) use ($event, $globalProvider): bool {
                    return $resource === 'resource'
                        && $eventType === $event::getEventName()
                        && $provider === $globalProvider
                        && $objectId === '8d0e43fd-d5d4-4b61-8963-e777c591cf0d'
                        && $eventOccurredOn === $event->getOccurredOn()
                        && $correlationId === 'correlation-id'
                        && $subscribeName === "{$globalProvider}.{$event->getPublicName()}"
                        && $topic === 'topic'
                        && $payload === $event->getPayload();
                }
            )
            ->andReturn($expectedMessage);

        $factory = new DomainEventMessageFactory($registry, $messageDtoFactory);

        $message = $factory->createFromDomainEvent($event, 'correlation-id');

        $this->assertSame($expectedMessage, $message);
    }

    public function testUsesProviderOverrideFromMetadata(): void
    {
        $globalProvider = 'globalProvider';
        $event          = new NullEvent('8d0e43fd-d5d4-4b61-8963-e777c591cf0d');
        $config         = [
            'resource' => 'resource',
            'provider' => 'provider',
            'topic'    => 'topic',
        ];

        $registry = MetadataRegistry::createFromArrayConfig(
            [
                $event::getEventName() => $config,
            ],
            $globalProvider
        );

        $expectedMessage = new NullMessage('topic');

        /** @var MessageFactoryInterface|MockInterface $messageDtoFactory */
        $messageDtoFactory = Mockery::mock(MessageFactoryInterface::class);
        $messageDtoFactory
            ->shouldReceive('create')
            ->once()
            ->withArgs(
                function (
                    string $resource,
                    string $eventType,
                    string $provider,
                    string $objectId,
                    DateTimeImmutable $eventOccurredOn,
                    string $correlationId,
                    string $subscribeName,
                    string $topic,
                    array $payload
                ) use ($event): bool {
                    return $provider === 'provider'
                        && $subscribeName === "provider.{$event->getPublicName()}"
                        && $topic === 'topic';
                }
            )
            ->andReturn($expectedMessage);

        $factory = new DomainEventMessageFactory($registry, $messageDtoFactory);

        $message = $factory->createFromDomainEvent($event, 'correlation-id');

        $this->assertSame($expectedMessage, $message);
    }
}
