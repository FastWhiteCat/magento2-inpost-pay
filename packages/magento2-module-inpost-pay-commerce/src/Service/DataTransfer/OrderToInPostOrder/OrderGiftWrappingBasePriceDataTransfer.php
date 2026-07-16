<?php

declare(strict_types=1);

namespace InPost\InPostPayCommerce\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPayCommerce\Provider\Order\Total\GiftWrappingTotalProvider;
use Magento\Sales\Model\Order;

class OrderGiftWrappingBasePriceDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly GiftWrappingTotalProvider $giftWrappingTotalProvider
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $orderBasePrice = $inPostOrder->getOrderDetails()->getOrderBasePrice();
        $this->updateOrderBasePriceWithGiftWrappings($orderBasePrice, $order);
    }

    private function updateOrderBasePriceWithGiftWrappings(PriceInterface $orderBasePrice, Order $order): void
    {
        $originalGross = $orderBasePrice->getGross();
        $originalNet = $orderBasePrice->getNet();
        $originalVat = $orderBasePrice->getVat();

        $orderBasePrice->setGross(
            DecimalCalculator::add(
                $originalGross,
                $this->giftWrappingTotalProvider->getGiftWrappingTotalGross($order)
            )
        );
        $orderBasePrice->setNet(
            DecimalCalculator::add(
                $originalNet,
                $this->giftWrappingTotalProvider->getGiftWrappingTotalNet($order)
            )
        );
        $orderBasePrice->setVat(
            DecimalCalculator::add(
                $originalVat,
                $this->giftWrappingTotalProvider->getGiftWrappingTotalTaxAmount($order)
            )
        );
    }
}
