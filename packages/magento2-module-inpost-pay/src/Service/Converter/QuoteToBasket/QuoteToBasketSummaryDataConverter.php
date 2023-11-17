<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;

class QuoteToBasketSummaryDataConverter implements QuoteToBasketDataConverterInterface
{
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
        foreach ($quote->getItems() as $item) {
            $qty = (float)$item->getQty();
            $regularPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getAmount();
            $regularPriceExclTax += round($qty * (float)$regularPrice->getBaseAmount(), 2);
            $regularPriceInclTax += round($qty * (float)$regularPrice->getValue(), 2);
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
        $currentDateTime = new DateTime('now', new DateTimeZone("UTC"));
        $currentTimestamp = strtotime($currentDateTime->format(DateTimeInterface::ATOM));

        $expirationDateTime = new DateTime();
        $expirationDateTime->setTimestamp($currentTimestamp + $this->iziApiConfigProvider->getBasketLifetime());

        return $expirationDateTime->format(DateTimeInterface::ATOM);
    }
}
