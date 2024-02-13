<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\MessagingCore\Broking\Dto\Sending\BrokingBatchResponse;
use Profesia\MessagingCore\Broking\Dto\Sending\GroupedMessagesCollection;
use Profesia\MessagingCore\Broking\MessageBrokerInterface;

class NullMessageBroker implements MessageBrokerInterface
{
    public function publish(GroupedMessagesCollection $collection): BrokingBatchResponse
    {
        return BrokingBatchResponse::createForMessagesWithBatchStatus(true, 'reason');
    }
}
