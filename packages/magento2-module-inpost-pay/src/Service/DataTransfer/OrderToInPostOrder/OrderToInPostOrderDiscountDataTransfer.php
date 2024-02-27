<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Sales\Model\Order;

class OrderToInPostOrderDiscountDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    /**
     * Append order details with calculated gross amount of discount between base product prices and final price
     *
     * @param Order $order
     * @param OrderInterface $inPostOrder
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $orderSummedProductsBaseGrossAmount = $this->getOrderSummedProductsBaseGrossAmount($inPostOrder);
        $orderBaseGrossAmount = $this->getOrderBaseGrossAmount($inPostOrder);

        $orderDiscountGrossAmount = DecimalCalculator::sub(
            $orderSummedProductsBaseGrossAmount,
            $orderBaseGrossAmount
        );

        $inPostOrder->getOrderDetails()->setOrderDiscount(abs($orderDiscountGrossAmount));
    }

    /**
     * @param OrderInterface $inPostOrder
     * @return float
     */
    private function getOrderSummedProductsBaseGrossAmount(OrderInterface $inPostOrder): float
    {
        $orderSummedProductsBaseGrossAmount = 0.00;

        foreach ($inPostOrder->getProducts() as $product) {
            $basePrice = $product->getBasePrice();
            $orderSummedProductsBaseGrossAmount = DecimalCalculator::add(
                $orderSummedProductsBaseGrossAmount,
                $basePrice->getGross()
            );
        }

        return $orderSummedProductsBaseGrossAmount;
    }

    /**
     * @param OrderInterface $inPostOrder
     * @return float
     */
    private function getOrderBaseGrossAmount(OrderInterface $inPostOrder): float
    {
        return $inPostOrder->getOrderDetails()->getOrderBasePrice()->getGross();
    }
}
