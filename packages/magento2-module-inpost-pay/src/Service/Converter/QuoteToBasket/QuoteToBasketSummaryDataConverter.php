<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use DateTime;
use DateTimeZone;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;

class QuoteToBasketSummaryDataConverter implements QuoteToBasketDataConverterInterface
{
    public const INPOST_DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider
    ) {
    }

    public function convert(Quote $quote): array
    {
        $address = $quote->getShippingAddress();
        $discountInclTax = DecimalCalculator::round($address->getDiscountAmount());
        $discountExclTax = DecimalCalculator::add(
            $address->getDiscountAmount(),
            $address->getDiscountTaxCompensationAmount()
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

        $summaryData = [
            Basket::BASKET_BASE_PRICE => [
                Basket::NET => $regularPriceExclTax,
                Basket::GROSS => $regularPriceInclTax,
                Basket::VAT => $regularPriceTax
            ],
            Basket::BASKET_FINAL_PRICE => [
                Basket::NET => $finalPriceExclTax,
                Basket::GROSS => $finalPriceInclTax,
                Basket::VAT => $finalPriceTax
            ],
            Basket::BASKET_PROMO_PRICE => [
                Basket::NET => $promoPriceExclTax,
                Basket::GROSS => $promoPriceInclTax,
                Basket::VAT => $promoPriceTax,
            ],
            Basket::CURRENCY => $quote->getQuoteCurrencyCode(),
            Basket::BASKET_ADDITIONAL_INFORMATION => '',
            Basket::PAYMENT_TYPE => $this->iziApiConfigProvider->getAcceptedPaymentTypes(),
            Basket::BASKET_NOTICE => null
        ];

        $basketExpirationDate = $this->calculateBasketExpirationDate();
        if ($basketExpirationDate) {
            $summaryData[Basket::BASKET_EXPIRATION_DATE] = $this->calculateBasketExpirationDate();
        }

        return $summaryData;
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
