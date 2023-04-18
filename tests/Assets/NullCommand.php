<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\Command\AbstractCommandFromMessage;

class NullCommand extends AbstractCommandFromMessage
{
    public function getPayload(): array
    {
        return [
            'NullCommand' => $this->getDecodedMessage()
        ];
    }
}