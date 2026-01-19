<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

interface RefundEventInterface
{
    /**
     * @param string $eventType
     * @param \InPost\InPostPay\Api\Data\Merchant\Refund\EventDataInterface $eventData
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function execute(
        string $eventType,
        \InPost\InPostPay\Api\Data\Merchant\Refund\EventDataInterface $eventData
    ): bool;
}
