<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderConsentsDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $orderId = (int)(is_scalar($order->getEntityId()) ? $order->getEntityId() : null);
        $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
        $inPostOrder->setConsents($inPostPayOrder->getAcceptedConsents());
    }
}
