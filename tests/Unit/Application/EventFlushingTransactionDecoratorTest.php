<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application;

use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\DddBackbone\Application\EventFlushingTransactionDecorator;
use Profesia\DddBackbone\Application\Event\DequeueEventInterface;
use Profesia\DddBackbone\Application\TransactionServiceInterface;
use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;

class EventFlushingTransactionDecoratorTest extends MockeryTestCase
{
    public function testCanStart(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('start')
            ->once();

        /** @var MockInterface|DequeueEventInterface $dequeueTrigger */
        $dequeueTrigger = Mockery::mock(DequeueEventInterface::class);

        $decorator = new EventFlushingTransactionDecorator(
            $decoratedObject,
            $dequeueTrigger
        );

        $decorator->start();
    }

    public function testCanCommit(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('commit')
            ->once();

        /** @var MockInterface|DequeueEventInterface $dequeueTrigger */
        $dequeueTrigger = Mockery::mock(DequeueEventInterface::class);

        $decorator = new EventFlushingTransactionDecorator(
            $decoratedObject,
            $dequeueTrigger
        );

        $decorator->commit();
    }

    public function testCanRollback(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('rollback')
            ->once();

        /** @var MockInterface|DequeueEventInterface $dequeueTrigger */
        $dequeueTrigger = Mockery::mock(DequeueEventInterface::class);

        $decorator = new EventFlushingTransactionDecorator(
            $decoratedObject,
            $dequeueTrigger
        );

        $decorator->rollback();
    }

    public function testCanPerformTransaction(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);

        /** @var MockInterface|DequeueEventInterface $dequeueTrigger */
        $dequeueTrigger = Mockery::mock(DequeueEventInterface::class);
        $dequeueTrigger
            ->shouldReceive('flush')
            ->once();

        $decorator = new EventFlushingTransactionDecorator(
            $decoratedObject,
            $dequeueTrigger
        );

        $callback = function () {
            return 1;
        };

        $decoratedObject
            ->shouldReceive('transactional')
            ->once()
            ->withArgs(
                [
                    $callback
                ]
            )->andReturn(1);

        $returnValue = $decorator->transactional(
            $callback
        );

        $this->assertEquals(1, $returnValue);
    }

    public function testWillNotFlushEventsOnException(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);

        /** @var MockInterface|DequeueEventInterface $dequeueTrigger */
        $dequeueTrigger = Mockery::mock(DequeueEventInterface::class);
        $dequeueTrigger
            ->shouldNotHaveBeenCalled();

        $decorator = new EventFlushingTransactionDecorator(
            $decoratedObject,
            $dequeueTrigger
        );

        $callback = function () {
            return 1;
        };

        $exception = new InvalidArgumentException('Test message');
        $decoratedObject
            ->shouldReceive('transactional')
            ->once()
            ->withArgs(
                [
                    $callback
                ]
            )->andThrow(
                $exception
            );

        $this->expectExceptionObject($exception);
        $decorator->transactional(
            $callback
        );
    }
}
