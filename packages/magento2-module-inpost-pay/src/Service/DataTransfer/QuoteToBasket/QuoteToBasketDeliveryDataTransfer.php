<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterfaceFactory;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Provider\Delivery\DeliveryDateProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToBasketDeliveryDataTransfer implements QuoteToBasketDataTransferInterface
{
    private const DEFAULT_COUNTRY_ID = 'PL';

    public function __construct(
        private readonly DeliveryInterfaceFactory $deliveryFactory,
        private readonly DeliveryOptionInterfaceFactory $deliveryOptionFactory,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager,
        private readonly NoticeInterfaceFactory $noticeFactory
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $shippingAddress = $quote->getShippingAddress();
        if (empty($shippingAddress->getCountryId())) {
            $shippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
        }

        if ($quote->isVirtual()) {
            $basket->setDelivery([]);
            $this->setBasketNoticeVirtualProducts($basket);
            return;
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProduct()->getIsVirtual()) {
                $this->setBasketNoticeVirtualProducts($basket);
                break;
            }
        }

        // @phpstan-ignore-next-line
        $shippingMethods = $this->shippingManager->estimateByExtendedAddress((int)$quote->getId(), $shippingAddress);
        $deliveries = $this->prepareMappedShippingMethodsData($shippingMethods);

        if (empty($deliveries)) {
            throw new LocalizedException(__('No delivery method is allowed for this basket.'));
        }

        $basket->setDelivery($deliveries);
    }

    /**
     * @param DeliveryInterface[] $quoteAvailableShippingMethods
     * @return array
     */
    private function prepareMappedShippingMethodsData(array $quoteAvailableShippingMethods): array
    {
        $deliveryData = [];
        foreach ($this->shipmentMappingConfigProvider->getAllDeliveryTypes() as $deliveryType) {
            $shippingMethod = $this->getDeliveryByTypeAndOption(
                $quoteAvailableShippingMethods,
                $deliveryType,
                ShipmentMappingConfigProvider::OPTION_STANDARD
            );
            if ($shippingMethod === null) {
                continue;
            }

            /** @var DeliveryInterface $delivery */
            $delivery = $this->deliveryFactory->create();
            $delivery->setDeliveryType($deliveryType);
            $delivery->setDeliveryDate($this->deliveryDateProvider->calculateDeliveryDate($shippingMethod));
            $deliverPrice = $delivery->getDeliveryPrice();
            $deliverPrice->setNet(DecimalCalculator::round((float)$shippingMethod->getPriceExclTax()));
            $deliverPrice->setGross(DecimalCalculator::round((float)$shippingMethod->getPriceInclTax()));
            $deliverPrice->setVat(DecimalCalculator::sub($deliverPrice->getGross(), $deliverPrice->getNet()));
            $delivery->setDeliveryPrice($deliverPrice);

            $freeShippingLimit = $this->getFreeShippingLimit($shippingMethod);
            if ($freeShippingLimit) {
                $delivery->setFreeDeliveryMinimumGrossPrice($freeShippingLimit);
            }

            $optionsData = [];
            foreach ($this->shipmentMappingConfigProvider->getNonStandardDeliveryOptions() as $optionCode) {
                $optionShippingMethod = $this->getDeliveryByTypeAndOption(
                    $quoteAvailableShippingMethods,
                    $deliveryType,
                    $optionCode
                );

                if ($optionShippingMethod === null) {
                    continue;
                }

                $deliveryOption = $this->getDeliveryOption($optionShippingMethod, $deliverPrice, $optionCode);
                $optionsData[] = $deliveryOption;
            }

            $delivery->setDeliveryOptions($optionsData);
            $deliveryData[] = $delivery;
        }

        return $deliveryData;
    }

    private function getDeliveryOption(
        ShippingMethodInterface $optionShippingMethod,
        PriceInterface $standardDeliveryPrice,
        string $optionCode
    ): DeliveryOptionInterface {
        /** @var DeliveryOptionInterface $deliveryOption */
        $deliveryOption = $this->deliveryOptionFactory->create();
        $deliveryOption->setDeliveryName((string)$optionShippingMethod->getMethodTitle());
        $deliveryOption->setDeliveryCodeValue($optionCode);
        $optionPrice = $deliveryOption->getDeliveryOptionPrice();

        $optionPriceNet = DecimalCalculator::round((float)$optionShippingMethod->getPriceExclTax());
        $optionPriceGross = DecimalCalculator::round((float)$optionShippingMethod->getPriceInclTax());
        $optionPriceVat = DecimalCalculator::sub($optionPriceGross, $optionPriceNet);

        $optionPriceNetDiff = DecimalCalculator::sub($optionPriceNet, $standardDeliveryPrice->getNet());
        $optionPriceGrossDiff = DecimalCalculator::sub($optionPriceGross, $standardDeliveryPrice->getGross());
        $optionPriceVatDiff = DecimalCalculator::sub($optionPriceVat, $standardDeliveryPrice->getVat());

        $optionPrice->setNet(max($optionPriceNetDiff, 0));
        $optionPrice->setGross(max($optionPriceGrossDiff, 0));
        $optionPrice->setVat(max($optionPriceVatDiff, 0));

        $deliveryOption->setDeliveryOptionPrice($optionPrice);

        return $deliveryOption;
    }

    private function getDeliveryByTypeAndOption(
        array $quoteAvailableShippingMethods,
        string $deliveryType,
        string $option
    ): ?ShippingMethodInterface {
        try {
            $mappedMethodCode = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                $deliveryType,
                $option
            );
            foreach ($quoteAvailableShippingMethods as $shippingMethod) {
                $allowedMethodCode = sprintf(
                    '%s_%s',
                    $shippingMethod->getCarrierCode(),
                    $shippingMethod->getMethodCode()
                );
                if ($shippingMethod instanceof ShippingMethodInterface && $allowedMethodCode === $mappedMethodCode) {
                    $mappedShippingMethod = $shippingMethod;
                    break;
                }
            }
        } catch (InPostPayInternalException $e) {
            $mappedShippingMethod = null;
        }

        return $mappedShippingMethod ?? null;
    }

    private function getFreeShippingLimit(ShippingMethodInterface $pickupPointShippingMethod): ?float
    {
        $limit = null;
        $method = (string)$pickupPointShippingMethod->getMethodCode();
        $code = (string)$pickupPointShippingMethod->getCarrierCode();
        if ($this->shipmentMappingConfigProvider->isFreeShippingEnabledForCarrier($code, $method)) {
            $limit = $this->shipmentMappingConfigProvider->getFreeShippingSubtotalForCarrier($code, $method);
        }

        return $limit;
    }

    private function setBasketNoticeVirtualProducts(BasketInterface $basket) {
        $summary = $basket->getSummary();
        $error = __('Order contains products that cannot be shipped.',)->render();
        if ($notice = $summary->getBasketNotice()) {
            $notice->setDescription($notice->getDescription() . PHP_EOL . $error);
        } else {
            /** @var NoticeInterface $notice */
            $notice = $this->noticeFactory->create();
            $notice->setType(NoticeInterface::ATTENTION);
            $notice->setDescription($error);
        }

        $summary->setBasketNotice($notice);
    }
}
