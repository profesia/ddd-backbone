<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Integration\Event;

use PHPUnit\Framework\TestCase;
use Profesia\DddBackbone\Application\EventFlushingTransactionDecorator;
use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;
use Profesia\DddBackbone\Infrastructure\Psr\LoggingTransactionDecorator;
use Profesia\DddBackbone\Test\Assets\NullDequeueDispatcher;
use Profesia\DddBackbone\Test\Assets\NullTransactionService;
use Psr\Log\NullLogger;

class TransactionServiceTest extends TestCase
{
    public function testWrapping(): void
    {
        $nullService            = new NullTransactionService();
        $eventFlushingDecorator = new EventFlushingTransactionDecorator(
            $nullService,
            new NullDequeueDispatcher()
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
