<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use Magento\Sales\Model\Order;

class OrderToInPostOrderDataTransfer
{
    public function transfer(Order $order): array
    {
        return [
            'order' => [
                'order_id' => $order->getIncrementId()
            ]
        ];
    }
}
