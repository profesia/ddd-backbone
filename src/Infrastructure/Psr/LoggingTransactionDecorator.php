<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Psr;

use Profesia\DddBackbone\Application\Log\Context;
use Profesia\DddBackbone\Application\TransactionServiceInterface;
use Profesia\DddBackbone\Domain\Exception\DomainException;
use Profesia\DddBackbone\Infrastructure\Utils\Backtrace\FormatsBacktrace;
use Psr\Log\LoggerInterface;

final class LoggingTransactionDecorator implements TransactionServiceInterface
{
    use FormatsBacktrace;

    public function __construct(
        private TransactionServiceInterface $transactionService,
        private LoggerInterface $logger,
        private string $errorMessage
    )
    {
    }

    public function start(): void
    {
        $this->transactionService->start();
    }

    public function commit(): void
    {
        $this->transactionService->commit();
    }

    public function rollback(): void
    {
        $this->transactionService->rollback();
    }

    public function transactional(callable $func): mixed
    {
        try {
             return $this->transactionService->transactional($func);
        } catch (DomainException $e) {
            $this->logger->error(
                $this->errorMessage,
                [
                    Context::MESSAGE_TYPE => self::class,
                    Context::EXCEPTION    => $e,
                    'stackTrace'          => self::formatBacktrace($e->getTrace()),
                ]
            );

            throw $e;
        }
    }
}
