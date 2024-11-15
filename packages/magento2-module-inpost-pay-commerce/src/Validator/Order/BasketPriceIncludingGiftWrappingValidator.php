<?php

declare(strict_types=1);

namespace InPost\InPostPayCommerce\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Validator\Order\BasketPriceValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use InPost\InPostPayCommerce\Provider\Quote\Total\GiftWrappingTotalProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasketPriceIncludingGiftWrappingValidator extends BasketPriceValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly GiftWrappingTotalProvider $giftWrappingTotalProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager
    ) {
        parent::__construct($shipmentMappingConfigProvider, $shippingManager);
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
        $address = $quote->getShippingAddress();
        $shippingMethod = $this->getSelectedShippingMethod($quote, $inPostOrder);
        $this->validateCurrency($quote, $inPostOrder);
        $this->validateGrossPrice($address, $shippingMethod, $inPostOrder->getOrderDetails()->getBasketPrice());
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

        $finalPriceInclTax = DecimalCalculator::add(
            $finalPriceInclTax,
            $this->getGiftWrappingTotalInclTax($address)
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

    private function getGiftWrappingTotalInclTax(Address $address): float
    {
        $giftWrappingTotalGross = 0.00;
        $totals = $address->getTotals();
        $giftWrappingTotal = $totals['giftwrapping'] ?? null;

        if ($giftWrappingTotal instanceof Total) {
            $giftWrappingTotalGross = $this->giftWrappingTotalProvider->getGiftWrappingTotalGross($giftWrappingTotal);
        }

        return $giftWrappingTotalGross;
    }
}
