<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class BasketPriceValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager
    ) {
    }

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        $address = $quote->getShippingAddress();
        $shippingMethod = $this->getSelectedShippingMethod($quote, $inPostOrder);
        $this->validateCurrency($quote, $inPostOrder);
        $this->validateGrossPrice($address, $shippingMethod, $inPostOrder->getOrderDetails()->getBasketPrice());
        $this->validateNetPrice($address, $shippingMethod, $inPostOrder->getOrderDetails()->getBasketPrice());
        $this->validateTaxPrice($address, $shippingMethod, $inPostOrder->getOrderDetails()->getBasketPrice());
    }

    /**
     * @param Address $address
     * @param ShippingMethodInterface $shippingMethod
     * @param PriceInterface $basketPrice
     * @return void
     * @throws LocalizedException
     */
    private function validateGrossPrice(
        Address $address,
        ShippingMethodInterface $shippingMethod,
        PriceInterface $basketPrice
    ): void {
        $discountInclTax = DecimalCalculator::round((float)$address->getDiscountAmount());
        $priceInclTaxWithShipping = DecimalCalculator::add(
            (float)$address->getSubtotalInclTax(),
            (float)$shippingMethod->getPriceInclTax()
        );
        $finalPriceInclTax = DecimalCalculator::round(
            DecimalCalculator::add($priceInclTaxWithShipping, $discountInclTax)
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
     * @param ShippingMethodInterface $shippingMethod
     * @param PriceInterface $basketPrice
     * @return void
     * @throws LocalizedException
     */
    private function validateNetPrice(
        Address $address,
        ShippingMethodInterface $shippingMethod,
        PriceInterface $basketPrice
    ): void {
        $discountExclTax = DecimalCalculator::add(
            (float)$address->getDiscountAmount(),
            (float)$address->getDiscountTaxCompensationAmount()
        );
        $priceExclTaxWithShipping = DecimalCalculator::add(
            (float)$address->getSubtotal(),
            (float)$shippingMethod->getPriceExclTax()
        );
        $finalPriceExclTax = DecimalCalculator::round(
            DecimalCalculator::add((float)$priceExclTaxWithShipping, $discountExclTax)
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
     * @param ShippingMethodInterface $shippingMethod
     * @param PriceInterface $basketPrice
     * @return void
     * @throws LocalizedException
     */
    private function validateTaxPrice(
        Address $address,
        ShippingMethodInterface $shippingMethod,
        PriceInterface $basketPrice
    ): void {
        $shippingPriceTax = DecimalCalculator::sub(
            (float)$shippingMethod->getPriceInclTax(),
            (float)$shippingMethod->getPriceExclTax()
        );

        $finalPriceTax = DecimalCalculator::round(
            DecimalCalculator::add((float)$address->getTaxAmount(), $shippingPriceTax)
        );

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
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     */
    private function validateCurrency(Quote $quote, OrderInterface $inPostOrder): void
    {
        if ($inPostOrder->getOrderDetails()->getCurrency() !== $quote->getQuoteCurrencyCode()) {
            throw new LocalizedException(
                __(
                    'Order currency is incorrect. Expected: %1 Received: %2',
                    $quote->getQuoteCurrencyCode(),
                    $inPostOrder->getOrderDetails()->getCurrency()
                )
            );
        }
    }

    /**
     * @param Quote $quote
     * @param OrderInterface $inPostOrder
     * @return ShippingMethodInterface
     * @throws LocalizedException
     */
    private function getSelectedShippingMethod(Quote $quote, OrderInterface $inPostOrder): ShippingMethodInterface
    {
        $quoteAvailableShippingMethods = $this->getQuoteAvailableShippingMethods($quote, $inPostOrder);
        $deliveryType = $inPostOrder->getDelivery()->getDeliveryType();
        $deliveryOption = $this->getSelectedDeliveryOption($inPostOrder);
        try {
            $mappedMethodCode = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                $deliveryType,
                $deliveryOption
            );
            foreach ($quoteAvailableShippingMethods as $shippingMethod) {
                $allowedMethodCode = sprintf(
                    '%s_%s',
                    $shippingMethod->getCarrierCode(),
                    $shippingMethod->getMethodCode()
                );
                if ($shippingMethod instanceof ShippingMethodInterface && $allowedMethodCode === $mappedMethodCode) {
                    return $shippingMethod;
                }
            }
        } catch (InPostPayInvalidConfigurationException $e) {
            $mappedShippingMethod = null;
        }

        if (empty($mappedShippingMethod)) {
            throw new LocalizedException(
                __(
                    'Selected shipping method is not available [%s %s].',
                    $deliveryType,
                    $deliveryOption
                )
            );
        }
    }

    private function getSelectedDeliveryOption(OrderInterface $inPostOrder): string
    {
        if (empty($inPostOrder->getDelivery()->getDeliveryCodes())) {
            return ShipmentMappingConfigProvider::OPTION_STANDARD;
        } else {
            return implode('', $inPostOrder->getDelivery()->getDeliveryCodes());
        }
    }

    /**
     * @param Quote $quote
     * @param OrderInterface $inPostOrder
     * @return ShippingMethodInterface[]
     */
    private function getQuoteAvailableShippingMethods(Quote $quote, OrderInterface $inPostOrder): array
    {
        $quoteId = (is_scalar($quote->getId())) ? (int)$quote->getId() : 0;
        $countryId = $inPostOrder->getDelivery()->getDeliveryAddress()->getCountryCode();
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCountryId($countryId);

        // @phpstan-ignore-next-line
        return $this->shippingManager->estimateByExtendedAddress($quoteId, $shippingAddress);
    }
}
