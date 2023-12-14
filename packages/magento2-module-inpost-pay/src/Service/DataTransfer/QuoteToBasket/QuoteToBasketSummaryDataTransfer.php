<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use DateTime;
use DateTimeZone;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Quote\Model\Quote;

class QuoteToBasketSummaryDataTransfer implements QuoteToBasketDataTransferInterface
{
    public const INPOST_DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $address = $quote->getShippingAddress();
        $discountInclTax = DecimalCalculator::round((float)$address->getDiscountAmount());
        $discountExclTax = DecimalCalculator::add(
            (float)$address->getDiscountAmount(),
            (float)$address->getDiscountTaxCompensationAmount()
        );

        $regularPriceInclTax = $this->getTotalQuoteItemsRegularPrice($quote, true);
        $regularPriceExclTax = $this->getTotalQuoteItemsRegularPrice($quote, false);
        $regularPriceTax = DecimalCalculator::sub($regularPriceInclTax, $regularPriceExclTax);

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

        $summary = $basket->getSummary();
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

        $summary->setCurrency($quote->getQuoteCurrencyCode());
        $summary->setBasketAdditionalInformation('');
        $summary->setPaymentType($this->iziApiConfigProvider->getAcceptedPaymentTypes());

        $basketExpirationDate = $this->calculateBasketExpirationDate();
        if ($basketExpirationDate) {
            $summary->setBasketExpirationDate($basketExpirationDate);
        }

        $basket->setSummary($summary);
    }

    private function calculateBasketExpirationDate(): ?string
    {
        $basketExpirationDate = null;
        $basketLifetime = $this->iziApiConfigProvider->getBasketLifetime();
        if ($basketLifetime) {
            $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
            $currentTimestamp = strtotime($currentDateTime->format(self::INPOST_DATE_FORMAT));

            $expirationDateTime = new DateTime();
            $expirationDateTime->setTimestamp($currentTimestamp + $basketLifetime);

            $basketExpirationDate = $expirationDateTime->format(self::INPOST_DATE_FORMAT);
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
