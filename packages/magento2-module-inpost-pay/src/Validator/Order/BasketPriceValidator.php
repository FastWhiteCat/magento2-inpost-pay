<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayInternalException;
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

    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        if ($inPostOrder->getDelivery()->getDeliveryType() === InPostDeliveryType::DIGITAL->value) {
            $billingAddress = $quote->getBillingAddress();
            $this->validateCurrency($quote, $inPostOrder);
            $this->validateGrossPrice($billingAddress, $inPostOrder->getOrderDetails()->getBasketPrice());
        } else {
            $shippingAddress = $quote->getShippingAddress();
            $shippingMethod = $this->getSelectedShippingMethod($quote, $inPostOrder);
            $this->validateCurrency($quote, $inPostOrder);
            $this->validateGrossPrice(
                $shippingAddress,
                $inPostOrder->getOrderDetails()->getBasketPrice(),
                $shippingMethod
            );
        }
    }

    /**
     * @param Address $address
     * @param PriceInterface|null $basketPrice
     * @param ShippingMethodInterface|null $shippingMethod
     * @return void
     * @throws LocalizedException
     */
    private function validateGrossPrice(
        Address $address,
        ?PriceInterface $basketPrice,
        ?ShippingMethodInterface $shippingMethod = null,
    ): void {
        $shippingCost = $shippingMethod ? (float)$shippingMethod->getPriceInclTax() : 0.00;

        $discountInclTax = DecimalCalculator::round((float)$address->getDiscountAmount());
        $priceInclTaxWithShipping = DecimalCalculator::add(
            (float)$address->getSubtotalInclTax(),
            $shippingCost
        );
        $finalPriceInclTax = DecimalCalculator::round(
            DecimalCalculator::add($priceInclTaxWithShipping, $discountInclTax)
        );

        if ($basketPrice === null) {
            throw new LocalizedException(__('Order final Gross value was not provided.'));
        }

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
        } catch (InPostPayInternalException $e) {
            $mappedMethodCode = null;
        }

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

        throw new LocalizedException(
            __(
                'Selected shipping method is not available [%1 %2].',
                $deliveryType,
                $deliveryOption
            )
        );
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
