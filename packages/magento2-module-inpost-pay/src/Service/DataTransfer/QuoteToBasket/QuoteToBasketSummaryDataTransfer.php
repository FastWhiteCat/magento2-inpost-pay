<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use DateTime;
use DateTimeZone;
use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;

class QuoteToBasketSummaryDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $summary = $this->transferQuoteSummary($quote, $basket);
        $summary->setCurrency($quote->getQuoteCurrencyCode());
        $summary->setBasketAdditionalInformation('');
        $summary->setPaymentType($this->iziApiConfigProvider->getAcceptedPaymentTypes());

        $basketExpirationDate = $this->calculateBasketExpirationDate();
        if ($basketExpirationDate) {
            $summary->setBasketExpirationDate($basketExpirationDate);
        }

        $summary->setFreeBasket(false);

        if ($summary->getBasketFinalPrice()->getGross() === 0.00) {
            $summary->setFreeBasket(true);
        }

        $basket->setSummary($summary);
    }

    private function transferQuoteSummary(Quote $quote, BasketInterface $basket): SummaryInterface
    {
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $discountInclTax = DecimalCalculator::round((float)$address->getDiscountAmount());
        $discountExclTax = DecimalCalculator::add(
            (float)$address->getDiscountAmount(),
            (float)$address->getDiscountTaxCompensationAmount()
        );

        $regularPriceInclTax = $this->getTotalQuoteItemsRegularPrice($quote, true);
        $regularPriceExclTax = $this->getTotalQuoteItemsRegularPrice($quote, false);
        $regularPriceTax = DecimalCalculator::sub($regularPriceInclTax, $regularPriceExclTax);

        if ((int)$quote->getItemsCount() === 0) {
            return $this->getEmptyQuoteSummary($quote, $basket);
        } else {
            $finalPriceExclTax = DecimalCalculator::round(
                DecimalCalculator::add((float)$address->getSubtotal(), $discountExclTax)
            );
            $finalPriceInclTax = DecimalCalculator::round(
                DecimalCalculator::add((float)$address->getSubtotalInclTax(), $discountInclTax)
            );
            $finalPriceTax = DecimalCalculator::round((float)$address->getTaxAmount());

            $promoPriceInclTax = DecimalCalculator::round((float)$address->getSubtotalInclTax());
            $promoPriceExclTax = DecimalCalculator::round((float)$address->getSubtotal());
            $promoPriceTax = DecimalCalculator::sub($promoPriceInclTax, $promoPriceExclTax);
        }

        $summary = $basket->getSummary();
        $this->fillSummaryWithPrices(
            $summary,
            $regularPriceExclTax,
            $regularPriceInclTax,
            $regularPriceTax,
            $finalPriceExclTax,
            $finalPriceInclTax,
            $finalPriceTax,
            $promoPriceExclTax,
            $promoPriceInclTax,
            $promoPriceTax
        );

        return $summary;
    }

    private function getEmptyQuoteSummary(Quote $quote, BasketInterface $basket): SummaryInterface
    {
        $regularPriceInclTax = $this->getTotalQuoteItemsRegularPrice($quote, true);
        $regularPriceExclTax = $this->getTotalQuoteItemsRegularPrice($quote, false);
        $regularPriceTax = DecimalCalculator::sub($regularPriceInclTax, $regularPriceExclTax);
        $totals = $quote->getTotals();

        $grandTotal = is_scalar($totals['grand_total']['value']) ? (float)$totals['grand_total']['value'] : 0;
        $tax = is_scalar($totals['tax']['value']) ? (float)$totals['tax']['value'] : 0;
        $subTotalIncTax = is_scalar($totals['subtotal']['value_incl_tax'])
            ? (float)$totals['subtotal']['value_incl_tax'] : 0;
        $subTotalExcTax = is_scalar($totals['subtotal']['value_excl_tax'])
            ? (float)$totals['subtotal']['value_excl_tax'] : 0;

        $finalPriceExclTax = DecimalCalculator::round(DecimalCalculator::sub($grandTotal, $tax));
        $finalPriceInclTax = DecimalCalculator::round($grandTotal);
        $finalPriceTax = DecimalCalculator::round($tax);

        $promoPriceInclTax = DecimalCalculator::round($subTotalIncTax);
        $promoPriceExclTax = DecimalCalculator::round($subTotalExcTax);
        $promoPriceTax = DecimalCalculator::sub($subTotalIncTax, $subTotalExcTax);

        $summary = $basket->getSummary();
        $this->fillSummaryWithPrices(
            $summary,
            $regularPriceExclTax,
            $regularPriceInclTax,
            $regularPriceTax,
            $finalPriceExclTax,
            $finalPriceInclTax,
            $finalPriceTax,
            $promoPriceExclTax,
            $promoPriceInclTax,
            $promoPriceTax
        );

        return $summary;
    }

    /**
     * @param SummaryInterface $summary
     * @param float $regularPriceExclTax
     * @param float $regularPriceInclTax
     * @param float $regularPriceTax
     * @param float $finalPriceExclTax
     * @param float $finalPriceInclTax
     * @param float $finalPriceTax
     * @param float $promoPriceExclTax
     * @param float $promoPriceInclTax
     * @param float $promoPriceTax
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function fillSummaryWithPrices(
        SummaryInterface $summary,
        float $regularPriceExclTax,
        float $regularPriceInclTax,
        float $regularPriceTax,
        float $finalPriceExclTax,
        float $finalPriceInclTax,
        float $finalPriceTax,
        float $promoPriceExclTax,
        float $promoPriceInclTax,
        float $promoPriceTax
    ): void {
        $basketBasePrice = $summary->getBasketBasePrice();
        $basketBasePrice->setNet($regularPriceExclTax);
        $basketBasePrice->setGross($regularPriceInclTax);
        $basketBasePrice->setVat($regularPriceTax);
        $summary->setBasketBasePrice($basketBasePrice);

        $basketFinalPrice = $summary->getBasketFinalPrice();
        $basketFinalPrice->setNet($finalPriceExclTax);
        $basketFinalPrice->setGross($finalPriceInclTax);
        $basketFinalPrice->setVat($finalPriceTax);
        $summary->setBasketFinalPrice($basketFinalPrice);

        $basketPromoPrice = $summary->getBasketPromoPrice();
        $basketPromoPrice->setNet($promoPriceExclTax);
        $basketPromoPrice->setGross($promoPriceInclTax);
        $basketPromoPrice->setVat($promoPriceTax);
        $summary->setBasketPromoPrice($basketPromoPrice);
    }

    private function calculateBasketExpirationDate(): ?string
    {
        $basketExpirationDate = null;
        $basketLifetime = $this->iziApiConfigProvider->getBasketLifetime();
        if ($basketLifetime) {
            $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
            $currentTimestamp = strtotime($currentDateTime->format(BasketInterface::INPOST_DATE_FORMAT));

            $expirationDateTime = new DateTime();
            $expirationDateTime->setTimestamp($currentTimestamp + $basketLifetime);

            $basketExpirationDate = $expirationDateTime->format(BasketInterface::INPOST_DATE_FORMAT);
        }

        return $basketExpirationDate;
    }

    private function getTotalQuoteItemsRegularPrice(Quote $quote, bool $inclTax): float
    {
        $regularPrice = 0.00;
        $quoteItems = $quote->getItems();
        if (empty($quoteItems)) {
            return $regularPrice;
        }

        foreach ($quoteItems as $item) {
            // @phpstan-ignore-next-line
            $unitPriceObj = $item->getProduct()->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
            $unitPrice = (float)(($inclTax) ? $unitPriceObj->getValue() : $unitPriceObj->getBaseAmount());
            $rowPrice = DecimalCalculator::mul((float)$item->getQty(), $unitPrice);
            $regularPrice = DecimalCalculator::add($regularPrice, $rowPrice);
        }

        return DecimalCalculator::round($regularPrice);
    }
}
