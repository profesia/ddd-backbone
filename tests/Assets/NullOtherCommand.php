<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\Command\AbstractCommandFromMessage;

class NullOtherCommand extends AbstractCommandFromMessage
{
    public function getPayload(): array
    {
        return [
            'NullOtherCommand' => true
        ];
    }
}