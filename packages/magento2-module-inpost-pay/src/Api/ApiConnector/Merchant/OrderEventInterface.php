<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderUpdateInterface;

interface OrderEventInterface
{
    public const EVENT_DATA = 'event_data';
    public const ORDER = 'order';
    public const ORDER_UPDATE = 'order_update';

    /**
     * @param string $orderId
     * @param string $eventId
     * @param string $eventDataTime
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface|null $phoneNumber
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface $eventData
     * @return \InPost\InPostPay\Api\Data\Merchant\OrderUpdateInterface
     */
    public function execute(
        string $orderId,
        string $eventId,
        string $eventDataTime,
        EventDataInterface $eventData,
        ?PhoneNumberInterface $phoneNumber = null
    ): OrderUpdateInterface;
}
