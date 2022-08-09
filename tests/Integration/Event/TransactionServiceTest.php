<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Event;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;
use Profesia\DddBackbone\Test\Integration\NullDispatcher;
use Profesia\DddBackbone\Test\Integration\NullTransactionService;
use Profesia\DddBackbone\Application\Event\QueuedDispatcherDecorator;
use Profesia\DddBackbone\Application\EventFlushingTransactionDecorator;
use Profesia\DddBackbone\Infrastructure\Psr\LoggingTransactionDecorator;
use Psr\Log\NullLogger;

class TransactionServiceTest extends TestCase
{
    public function testWrapping(): void
    {
        $nullService            = new NullTransactionService();
        $eventFlushingDecorator = new EventFlushingTransactionDecorator(
            $nullService,
            new QueuedDispatcherDecorator(
                new NullDispatcher()
            )
        );

        $loggingDecorator = new LoggingTransactionDecorator(
            $eventFlushingDecorator,
            new NullLogger(),
            'Test error message.'
        );

        $okCallback = function () {
            return 1;
        };

        $returnValue = $loggingDecorator->transactional($okCallback);
        $this->assertEquals(1, $returnValue);

        $exception         = new InvalidArgumentException('Test message');
        $exceptionCallback = function () use ($exception) {
            throw $exception;
        };

        $this->expectExceptionObject($exception);
        $loggingDecorator->transactional($exceptionCallback);
    }
}
