<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Model\Dto\Order as DtoOrder;
use InPost\InPostPay\Model\Dto\Order\BasketPrice;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class BasketPriceValidator implements OrderValidatorInterface
{

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, DtoOrder $orderDto): void
    {
        $address = $quote->getShippingAddress();
        $this->validateCurrency($quote, $orderDto);
        $this->validateGrossPrice($address, $orderDto->getOrderDetails()->getBasketPrice());
        $this->validateNetPrice($address, $orderDto->getOrderDetails()->getBasketPrice());
        $this->validateTaxPrice($address, $orderDto->getOrderDetails()->getBasketPrice());
    }

    /**
     * @param Address $address
     * @param BasketPrice $basketPrice
     * @return void
     * @throws LocalizedException
     */
    private function validateGrossPrice(Address $address, BasketPrice $basketPrice): void
    {
        $discountInclTax = DecimalCalculator::round((float)$address->getDiscountAmount());
        $finalPriceInclTax = DecimalCalculator::round(
            DecimalCalculator::add((float)$address->getSubtotalInclTax(), $discountInclTax)
        );

        if ($basketPrice->getGross() !== $finalPriceInclTax) {
            throw new LocalizedException(
                __(
                    'Order final Gross value is incorrect. Expected: %1 Received: %2',
                    $finalPriceInclTax,
                    $basketPrice->getGross()
                )
            );
        }
    }

    /**
     * @param Address $address
     * @param BasketPrice $basketPrice
     * @return void
     * @throws LocalizedException
     */
    private function validateNetPrice(Address $address, BasketPrice $basketPrice): void
    {
        $discountExclTax = DecimalCalculator::add(
            (float)$address->getDiscountAmount(),
            (float)$address->getDiscountTaxCompensationAmount()
        );
        $finalPriceExclTax = DecimalCalculator::round(
            DecimalCalculator::add((float)$address->getSubtotal(), $discountExclTax)
        );

        if ($basketPrice->getNet() !== $finalPriceExclTax) {
            throw new LocalizedException(
                __(
                    'Order final Net value is incorrect. Expected: %1 Received: %2',
                    $finalPriceExclTax,
                    $basketPrice->getNet()
                )
            );
        }
    }

    /**
     * @param Address $address
     * @param BasketPrice $basketPrice
     * @return void
     * @throws LocalizedException
     */
    private function validateTaxPrice(Address $address, BasketPrice $basketPrice): void
    {
        $finalPriceTax = DecimalCalculator::round((float)$address->getTaxAmount());
        if ($basketPrice->getVat() !== $finalPriceTax) {
            throw new LocalizedException(
                __(
                    'Order final Tax value is incorrect. Expected: %1 Received: %2',
                    $finalPriceTax,
                    $basketPrice->getVat()
                )
            );
        }
    }

    /**
     * @param Quote $quote
     * @param DtoOrder $orderDto
     * @return void
     * @throws LocalizedException
     */
    private function validateCurrency(Quote $quote, DtoOrder $orderDto): void
    {
        if ($orderDto->getOrderDetails()->getCurrency() !== $quote->getQuoteCurrencyCode()) {
            throw new LocalizedException(
                __(
                    'Order currency is incorrect. Expected: %1 Received: %2',
                    $quote->getQuoteCurrencyCode(),
                    $orderDto->getOrderDetails()->getCurrency()
                )
            );
        }
    }
}
