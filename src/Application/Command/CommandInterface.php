<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command;

use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

interface CommandInterface
{
    /**
     * @param ReceivedMessage $message
     * @return static
     */
    public static function createFromReceivedMessage(ReceivedMessage $message): self;

    public function getPayload(): array;
}
