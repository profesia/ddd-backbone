<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Psr;

use Profesia\DddBackbone\Application\TransactionServiceInterface;
use Profesia\DddBackbone\Domain\Exception\DomainException;
use Profesia\DddBackbone\Infrastructure\Utils\Backtrace\FormatsBacktrace;
use Psr\Log\LoggerInterface;

class LoggingTransactionDecorator implements TransactionServiceInterface
{
    use FormatsBacktrace;

    private const MESSAGE_TYPE = 'message_type';
    private const EXCEPTION    = 'exception';

    private TransactionServiceInterface $transactionService;
    private LoggerInterface $logger;

    public function __construct(TransactionServiceInterface $transactionService, LoggerInterface $logger)
    {
        $this->transactionService = $transactionService;
        $this->logger             = $logger;
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
                'Application transaction logger.',
                [
                    self::MESSAGE_TYPE => self::class,
                    self::EXCEPTION    => $e,
                    'stackTrace'          => static::formatBacktrace($e->getTrace()),
                ]
            );

            throw $e;
        }
    }
}
