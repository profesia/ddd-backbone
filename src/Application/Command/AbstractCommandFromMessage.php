<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command;

use Profesia\MessagingCore\Broking\Dto\ReceivedMessage;

abstract class AbstractCommandFromMessage implements CommandInterface
{
    private array $decodedMessage = [];

    public function __construct(ReceivedMessage $receivedMessage)
    {
        $this->decodedMessage = $receivedMessage->getDecodedMessage();
    }

    protected function getDecodedMessage(): array
    {
        return $this->decodedMessage;
    }
}