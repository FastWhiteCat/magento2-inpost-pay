<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use DateTime;
use DateTimeZone;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
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
        $discountInclTax = $address->getDiscountAmount();
        $discountExclTax = $address->getDiscountAmount() + $address->getDiscountTaxCompensationAmount();
        $regularPriceInclTax = 0.00;
        $regularPriceExclTax = 0.00;
        $quoteItems = $quote->getItems();
        if ($quoteItems) {
            foreach ($quote->getItems() as $item) {
                $qty = (float)$item->getQty();
                // @phpstan-ignore-next-line
                $regularPrice = $item->getProduct()
                    ->getPriceInfo()
                    ->getPrice(RegularPrice::PRICE_CODE)
                    ->getAmount();
                $regularPriceExclTax += round($qty * (float)$regularPrice->getBaseAmount(), 2);
                $regularPriceInclTax += round($qty * (float)$regularPrice->getValue(), 2);
            }
        }

        return [
            Basket::BASKET_BASE_PRICE => [
                Basket::NET => $regularPriceExclTax,
                Basket::GROSS => $regularPriceInclTax,
                Basket::VAT => $regularPriceInclTax - $regularPriceExclTax
            ],
            Basket::BASKET_FINAL_PRICE => [
                Basket::NET => (float)$address->getSubtotal() + $discountExclTax,
                Basket::GROSS => (float)$address->getSubtotalInclTax() + $discountInclTax,
                Basket::VAT => (float)($address->getTaxAmount() - $address->getDiscountTaxCompensationAmount())
            ],
            Basket::BASKET_PROMO_PRICE => [
                Basket::NET => (float)$address->getSubtotal(),
                Basket::GROSS => (float)$address->getSubtotalInclTax(),
                Basket::VAT => (float)($address->getSubtotalInclTax() - $address->getSubtotal()),
            ],
            Basket::CURRENCY => $quote->getQuoteCurrencyCode(),
            Basket::BASKET_EXPIRATION_DATE => $this->calculateBasketExpirationDate(),
            Basket::BASKET_ADDITIONAL_INFORMATION => '',
            Basket::PAYMENT_TYPE => $this->iziApiConfigProvider->getAcceptedPaymentTypes(),
            Basket::BASKET_NOTICE => null
        ];
    }

    private function calculateBasketExpirationDate(): string
    {
        $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $currentTimestamp = strtotime($currentDateTime->format(self::INPOST_DATE_FORMAT));

        $expirationDateTime = new DateTime();
        $expirationDateTime->setTimestamp($currentTimestamp + $this->iziApiConfigProvider->getBasketLifetime());

        return $expirationDateTime->format(self::INPOST_DATE_FORMAT);
    }
}
