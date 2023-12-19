<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\DataTransfer;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

interface OrderToInPostOrderDataTransferInterface
{
    /**
     * @param Order $order
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     */
    public function transfer(Order $order, OrderInterface $inPostOrder): void;
}
