<?php

declare(strict_types=1);

namespace InPost\InPostPayCommerce\Provider\Quote\Total;

use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Quote\Model\Quote\Address\Total;

class GiftWrappingTotalProvider
{
    public function getGiftWrappingTotalGross(Total $giftWrappingTotal): float
    {
        $gwPriceInclTax = $giftWrappingTotal->getData('gw_price_incl_tax');
        $gwPriceInclTax = is_scalar($gwPriceInclTax) ? (float)$gwPriceInclTax : 0.00;

        $gwItemPriceInclTax = $giftWrappingTotal->getData('gw_items_price_incl_tax');
        $gwItemPriceInclTax = is_scalar($gwItemPriceInclTax) ? (float)$gwItemPriceInclTax : 0.00;

        return DecimalCalculator::add($gwPriceInclTax, $gwItemPriceInclTax);
    }

    public function getGiftWrappingTotalTaxAmount(Total $giftWrappingTotal): float
    {
        $gwTaxAmountTax = $giftWrappingTotal->getData('gw_tax_amount');
        $gwTaxAmountTax = is_scalar($gwTaxAmountTax) ? (float)$gwTaxAmountTax : 0.00;

        $gwItemTaxAmountTax = $giftWrappingTotal->getData('gw_items_tax_amount');
        $gwItemTaxAmountTax = is_scalar($gwItemTaxAmountTax) ? (float)$gwItemTaxAmountTax : 0.00;

        return DecimalCalculator::add($gwTaxAmountTax, $gwItemTaxAmountTax);
    }

    public function getGiftWrappingTotalNet(Total $giftWrappingTotal): float
    {
        return DecimalCalculator::sub(
            $this->getGiftWrappingTotalGross($giftWrappingTotal),
            $this->getGiftWrappingTotalTaxAmount($giftWrappingTotal)
        );
    }
}
