<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderDataTransfer
{
    /**
     * @param Order $order
     * @param InPostOrderInterface $inPostOrder
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function transfer(Order $order, InPostOrderInterface $inPostOrder): void
    {
        $orderDetails = $inPostOrder->getOrderDetails();
        $orderDetails->setOrderComments(
            'TODO in INPAY-18::transfer data from Magento Order into InPostOrder'
        );
    }
}
