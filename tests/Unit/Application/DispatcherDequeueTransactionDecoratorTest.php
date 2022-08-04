<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application;

use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\DddBackbone\Application\DispatcherDequeueTransactionDecorator;
use Profesia\DddBackbone\Application\Event\DequeueEventInterface;
use Profesia\DddBackbone\Application\TransactionServiceInterface;

class DispatcherDequeueTransactionDecoratorTest extends MockeryTestCase
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

        $decorator = new DispatcherDequeueTransactionDecorator(
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

        $decorator = new DispatcherDequeueTransactionDecorator(
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

        $decorator = new DispatcherDequeueTransactionDecorator(
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

        $decorator = new DispatcherDequeueTransactionDecorator(
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
}
