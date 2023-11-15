<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

interface OrderLockerServiceInterface
{
    /**
     * @param Order $order
     * @param string $lockerId
     * @return void
     * @throws LocalizedException
     */
    public function setLockerIdForOrder(Order $order, string $lockerId): void;
}
