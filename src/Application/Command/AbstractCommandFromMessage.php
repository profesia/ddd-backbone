<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command;

use Profesia\MessagingCore\Broking\Dto\Receiving\ReceivedMessageInterface;

abstract class AbstractCommandFromMessage implements CommandInterface
{
    /** @var mixed[]  */
    private array $decodedMessage = [];

    public function __construct(ReceivedMessageInterface $receivedMessage)
    {
        $this->decodedMessage = $receivedMessage->getDecodedMessage();
    }

    /**
     * @return mixed[]
     */
    protected function getDecodedMessage(): array
    {
        return $this->decodedMessage;
    }
}