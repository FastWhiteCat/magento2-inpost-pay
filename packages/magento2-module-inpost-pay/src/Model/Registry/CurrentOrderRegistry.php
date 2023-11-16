<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Registry;

use Magento\Sales\Model\Order;

class CurrentOrderRegistry
{
    private ?Order $order = null;

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }
}
