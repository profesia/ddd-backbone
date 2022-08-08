<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Infrastructure\Psr;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\EventFlushingTransactionDecorator;
use Profesia\DddBackbone\Application\Event\DequeueEventInterface;
use Profesia\DddBackbone\Application\TransactionServiceInterface;
use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;
use Profesia\DddBackbone\Infrastructure\Psr\LoggingTransactionDecorator;
use Profesia\DddBackbone\Infrastructure\Utils\Backtrace\FormatsBacktrace;
use Psr\Log\LoggerInterface;

class LoggingTransactionDecoratorTest extends MockeryTestCase
{
    use FormatsBacktrace;

    public function testCanStart(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('start')
            ->once();

        /** @var MockInterface|LoggerInterface $logger */
        $logger = Mockery::mock(LoggerInterface::class);

        $decorator = new LoggingTransactionDecorator(
            $decoratedObject,
            $logger,
            'Test logger message.'
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

        /** @var MockInterface|LoggerInterface $logger */
        $logger = Mockery::mock(LoggerInterface::class);

        $decorator = new LoggingTransactionDecorator(
            $decoratedObject,
            $logger,
            'Test logger message.'
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

        /** @var MockInterface|LoggerInterface $logger */
        $logger = Mockery::mock(LoggerInterface::class);

        $decorator = new LoggingTransactionDecorator(
            $decoratedObject,
            $logger,
            'Test logger message.'
        );

        $decorator->rollback();
    }

    public function testCanPerformTransaction(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);

        /** @var MockInterface|LoggerInterface $logger */
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotHaveBeenCalled();

        $decorator = new LoggingTransactionDecorator(
            $decoratedObject,
            $logger,
            'Test logger message.'
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

    public function testCanHandleExceptionDuringTransaction(): void
    {
        $loggerMessage = 'Test logger message.';

        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);

        /** @var MockInterface|LoggerInterface $logger */
        $logger = Mockery::mock(LoggerInterface::class);

        $decorator = new LoggingTransactionDecorator(
            $decoratedObject,
            $logger,
            $loggerMessage
        );

        $exception = new InvalidArgumentException('Test message');
        $callback = function () use ($exception) {
            throw $exception;
        };

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

        $logger
            ->shouldReceive('error')
            ->once()
            ->withArgs(
                [
                    $loggerMessage,
                    [
                        'message_type' => LoggingTransactionDecorator::class,
                        'exception'    => $exception,
                        'stackTrace'   => self::formatBacktrace($exception->getTrace())
                    ]
                ]
            );

        $this->expectExceptionObject($exception);
        $decorator->transactional($callback);
    }
}
