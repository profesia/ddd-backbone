<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Doctrine;

use Throwable;
use Doctrine\ORM\EntityManagerInterface;
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

            $this->commit();
        } catch (Throwable $e) {
            try {
                $this->rollback();
            } catch (Throwable $rollbackException) {
                throw RollbackFailedException::chain($rollbackException, $e);
            }

            throw $e;
        }

        return $result ?? true;
    }
}
