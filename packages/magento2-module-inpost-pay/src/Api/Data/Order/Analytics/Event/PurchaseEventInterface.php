<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Order\Analytics\Event;

use Magento\Sales\Model\Order;

interface PurchaseEventInterface
{
    /**
     * @return string
     */
    public function getEventCode(): string;

    /**
     * @param Order $order
     * @return array
     */
    public function getEventData(Order $order): array;
}
