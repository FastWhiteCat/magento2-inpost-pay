<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderDataTransfer
{
    public function transfer(Order $order, InPostOrderInterface $inPostOrder): void
    {
        $orderDetails = $inPostOrder->getOrderDetails();
    }
}
