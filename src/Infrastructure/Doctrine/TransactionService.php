<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Doctrine;

use Throwable;
use Doctrine\ORM\EntityManagerInterface;
use Profesia\DddBackbone\Application\Exception\TransactionServiceException;
use Profesia\DddBackbone\Application\TransactionServiceInterface;

class TransactionService implements TransactionServiceInterface
{
    private EntityManagerInterface $entityManager;
    private bool $allowNullResult;

    public function __construct(EntityManagerInterface $entityManager, bool $allowNullResult = false)
    {
        $this->entityManager = $entityManager;
        $this->allowNullResult = $allowNullResult;
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
            $commitException = TransactionServiceException::createFromThrowable($e);
            try {
                $this->rollback();
            } catch (Throwable $rollbackException) {
                throw $commitException->wrap($rollbackException, $commitException);
            }

            throw $commitException;
        }

        return $this->allowNullResult ? $result : ($result ?? true);
    }
}
