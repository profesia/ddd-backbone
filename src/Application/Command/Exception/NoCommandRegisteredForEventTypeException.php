<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Exception;

use Profesia\DddBackbone\Application\Exception\AbstractApplicationException;

final class NoCommandRegisteredForEventTypeException extends AbstractApplicationException
{

}