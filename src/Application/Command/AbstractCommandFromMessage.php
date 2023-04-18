<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command;

use Profesia\MessagingCore\Broking\Dto\ReceivedMessageInterface;

abstract class AbstractCommandFromMessage implements CommandInterface
{
    private array $decodedMessage = [];

    public function __construct(ReceivedMessageInterface $receivedMessage)
    {
        $this->decodedMessage = $receivedMessage->getDecodedMessage();
    }

    protected function getDecodedMessage(): array
    {
        return $this->decodedMessage;
    }
}