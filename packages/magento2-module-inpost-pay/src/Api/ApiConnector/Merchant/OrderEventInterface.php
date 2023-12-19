<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface;
use InPost\InPostPay\Api\Data\UpdateOrderResponseInterface;

interface OrderEventInterface
{
    public const EVENT_DATA = 'event_data';
    public const ORDER = 'order';
    public const INPOST_PAY_ORDER_STATUS = 'inpost_pay_order_status';

    /**
     * @param string $orderId
     * @param string $eventId
     * @param string $eventDataTime
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface|null $phoneNumber
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface $eventData
     * @return \InPost\InPostPay\Api\Data\UpdateOrderResponseInterface
     */
    public function execute(
        string $orderId,
        string $eventId,
        string $eventDataTime,
        EventDataInterface $eventData,
        ?PhoneNumberInterface $phoneNumber = null
    ): UpdateOrderResponseInterface;
}
