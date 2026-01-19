<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\RefundEventInterface;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventDataInterface;

class RefundEvent implements RefundEventInterface
{
    /**
     * @param string $eventType
     * @param EventDataInterface $eventData
     * @return bool
     */
    public function execute(string $eventType, EventDataInterface $eventData): bool
    {
        // TODO: Implement execute() method.
    }
}
