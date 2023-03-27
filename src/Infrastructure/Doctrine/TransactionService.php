<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Doctrine;

use Throwable;
use Doctrine\ORM\EntityManagerInterface;
use Profesia\DddBackbone\Application\TransactionServiceInterface;

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
            $this->rollback();

            throw $e;
        }

        return $result ?? true;
    }
}
