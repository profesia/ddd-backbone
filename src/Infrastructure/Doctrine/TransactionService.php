<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Doctrine;

use Throwable;
use Doctrine\ORM\EntityManagerInterface;
use Profesia\DddBackbone\Application\Exception\TransactionServiceException;
use Profesia\DddBackbone\Application\TransactionServiceInterface;
use Profesia\DddBackbone\Infrastructure\Doctrine\Exception\RollbackFailedException;

class TransactionService implements TransactionServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function start(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    /**
     * @param callable $func
     *
     * @return mixed
     * @throws Throwable
     */
    public function transactional(callable $func)
    {
        $this->start();

        try {
            $result = call_user_func($func, $this);
        } catch (Throwable $e) {
            $this->tryRollback($e);

            throw $e;
        }

        try {
            $this->commit();
        } catch (Throwable $e) {
            $this->tryRollback($e);

            throw TransactionServiceException::createFromThrowable($e);
        }

        return $result ?? true;
    }

    private function tryRollback(Throwable $triggeringException): void
    {
        try {
            $this->rollback();
        } catch (Throwable $rollbackException) {
            throw RollbackFailedException::createFromThrowables($rollbackException, $triggeringException);
        }
    }
}
