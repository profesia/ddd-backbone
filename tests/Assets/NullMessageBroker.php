<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\MessagingCoreContracts\Broking\Dto\Sending\BrokingBatchResponse;
use Profesia\MessagingCoreContracts\Broking\Dto\Sending\GroupedMessagesCollection;
use Profesia\MessagingCoreContracts\Broking\MessageBrokerInterface;

class NullMessageBroker implements MessageBrokerInterface
{
    public function publish(GroupedMessagesCollection $collection): BrokingBatchResponse
    {
        return BrokingBatchResponse::createForMessagesWithBatchStatus(true, 'reason');
    }
}
