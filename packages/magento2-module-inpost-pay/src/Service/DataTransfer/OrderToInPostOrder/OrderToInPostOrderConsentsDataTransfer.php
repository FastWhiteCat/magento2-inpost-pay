<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderConsentsDataTransfer implements OrderToInPostOrderDataTransferInterface
{

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        // TODO: Implement transfer() method.
    }
}
