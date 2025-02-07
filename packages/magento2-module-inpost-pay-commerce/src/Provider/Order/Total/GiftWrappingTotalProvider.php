<?php

declare(strict_types=1);

namespace InPost\InPostPayCommerce\Provider\Order\Total;

use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Sales\Model\Order;

class GiftWrappingTotalProvider
{
    public function getGiftWrappingTotalGross(Order $order): float
    {
        $gwPriceInclTax = $order->getData('gw_price_incl_tax');
        $gwPriceInclTax = is_scalar($gwPriceInclTax) ? (float)$gwPriceInclTax : 0.00;

        $gwItemPriceInclTax = $order->getData('gw_items_price_incl_tax');
        $gwItemPriceInclTax = is_scalar($gwItemPriceInclTax) ? (float)$gwItemPriceInclTax : 0.00;

        return DecimalCalculator::add($gwPriceInclTax, $gwItemPriceInclTax);
    }

    public function getGiftWrappingTotalTaxAmount(Order $order): float
    {
        $gwTaxAmountTax = $order->getData('gw_tax_amount');
        $gwTaxAmountTax = is_scalar($gwTaxAmountTax) ? (float)$gwTaxAmountTax : 0.00;

        $gwItemTaxAmountTax = $order->getData('gw_items_tax_amount');
        $gwItemTaxAmountTax = is_scalar($gwItemTaxAmountTax) ? (float)$gwItemTaxAmountTax : 0.00;

        return DecimalCalculator::add($gwTaxAmountTax, $gwItemTaxAmountTax);
    }

    public function getGiftWrappingTotalNet(Order $order): float
    {
        return DecimalCalculator::sub(
            $this->getGiftWrappingTotalGross($order),
            $this->getGiftWrappingTotalTaxAmount($order)
        );
    }
}
