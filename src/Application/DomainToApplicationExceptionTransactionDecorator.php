<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application;

use Profesia\DddBackbone\Application\Exception\AbstractApplicationException;
use Profesia\DddBackbone\Application\Exception\BadConfigurationException;
use Profesia\DddBackbone\Application\Exception\GenericApplicationException;
use Profesia\DddBackbone\Domain\Exception\AbstractDomainException;

final class DomainToApplicationExceptionTransactionDecorator implements TransactionServiceInterface
{
    private TransactionServiceInterface $decoratedObject;
    private string                      $exceptionClass;

    public function __construct(
        TransactionServiceInterface $decoratedObject,
        ?string $classNameOverride = null
    )
    {
        $this->decoratedObject = $decoratedObject;
        $parentClass           = AbstractApplicationException::class;

        if ($classNameOverride !== null) {
            if (class_exists($classNameOverride) === false) {
                throw new BadConfigurationException("Class: [$classNameOverride] does not exist");
            }

            if (is_subclass_of($classNameOverride, $parentClass) === false) {
                throw new BadConfigurationException("Class: [$classNameOverride] is not a subclass of [$parentClass] class");
            }

            $this->exceptionClass = $classNameOverride;
        } else {
            $this->exceptionClass = GenericApplicationException::class;
        }
    }

    public function start(): void
    {
        $this->decoratedObject->start();
    }

    public function commit(): void
    {
        $this->decoratedObject->commit();
    }

    public function rollback(): void
    {
        $this->decoratedObject->rollback();
    }

    /**
     * @param callable $func
     * @return mixed
     * @throws AbstractApplicationException
     */
    public function transactional(callable $func)
    {
        try {
            return $this->decoratedObject->transactional($func);
        } catch (AbstractDomainException $e) {
            /**
             * @phpstan-ignore-next-line
             * */
            throw new $this->exceptionClass(
                $e->getMessage()
            );
        }
    }
}