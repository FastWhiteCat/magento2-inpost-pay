<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface;
use InPost\InPostPay\Api\Data\UpdateOrderResponseInterface;

interface OrderEventInterface
{
    /**
     * @param string $orderId
     * @param string $eventId
     * @param string $eventDataTime
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface $phoneNumber
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface $eventData
     * @return \InPost\InPostPay\Api\Data\UpdateOrderResponseInterface
     */
    public function execute(
        string $orderId,
        string $eventId,
        string $eventDataTime,
        PhoneNumberInterface $phoneNumber,
        EventDataInterface $eventData
    ): UpdateOrderResponseInterface;
}
