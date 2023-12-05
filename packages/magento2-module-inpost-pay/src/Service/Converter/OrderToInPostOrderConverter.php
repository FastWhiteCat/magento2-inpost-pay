<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter;

use Magento\Sales\Model\Order;

class OrderToInPostOrderConverter
{
    public function convert(Order $order): array
    {
        return [
            'order' => [
                'order_id' => $order->getIncrementId()
            ]
        ];
    }
}
