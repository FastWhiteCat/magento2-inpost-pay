<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Enum\InPostDeliveryOption;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayInternalException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ShipmentMappingConfigProvider
{
    public const DEFAULT_DELIVERY_DEADLINE = 7;
    public const DEFAULT_DIGITAL_DELIVERY_DEADLINE = 86400;

    public const OPTION_STANDARD = 'STANDARD';
    private const XML_PATH_DELIVERY_MAPPING_PATTERN = 'payment/inpost_pay/inpost_%s_%s_mapping';
    private const XML_PATH_OTHER_DELIVERY_MAPPING_PATTERN = 'payment/inpost_pay/other_inpost_%s_%s_mapping';
    private const XML_PATH_DELIVERY_DEADLINE_IN_DAYS = 'payment/inpost_pay/delivery_deadline_in_days';
    private const XML_PATH_USE_COLLECT_ADDRESS_TOTALS = 'payment/inpost_pay/estimate_with_collect_address_totals';
    private const XML_PATH_DIGITAL_DELIVERY_DEADLINE_IN_SEC = 'payment/inpost_pay/digital_delivery_deadline_in_days';
    private const XML_PATH_FREE_SHIPPING_ENABLED_PATTERN = 'carriers/%s/free_shipping_enable';
    private const XML_PATH_FREE_SHIPPING_SUBTOTAL_PATTERN = 'carriers/%s/free_shipping_subtotal';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param string $deliveryType
     * @param string $option
     * @param int|null $storeId
     * @return string
     * @throws InPostPayInternalException
     */
    public function getCarrierMethodCodeForOptions(string $deliveryType, string $option, ?int $storeId = null): string
    {
        $otherCarrierConfigPattern = self::XML_PATH_OTHER_DELIVERY_MAPPING_PATTERN;
        $otherCarrierConfigPath = sprintf($otherCarrierConfigPattern, strtolower($deliveryType), strtolower($option));
        $carrier = $this->scopeConfig->getValue($otherCarrierConfigPath, ScopeInterface::SCOPE_STORE, $storeId);

        if (empty($carrier) || !is_scalar($carrier)) {
            $carrierConfigPattern = self::XML_PATH_DELIVERY_MAPPING_PATTERN;
            $carrierConfigPath = sprintf($carrierConfigPattern, strtolower($deliveryType), strtolower($option));
            $carrier = $this->scopeConfig->getValue($carrierConfigPath, ScopeInterface::SCOPE_STORE, $storeId);
        }

        if (empty($carrier) || !is_scalar($carrier)) {
            throw new InPostPayInternalException(
                __('InPost Courier not mapped for delivery type: %1 with option: %2', $deliveryType, $option)
            );
        }

        return (string)$carrier;
    }

    /**
     * @return string[]
     */
    public function getAllDeliveryTypes(): array
    {
        return [InPostDeliveryType::APM->name, InPostDeliveryType::COURIER->name];
    }

    /**
     * @param bool $withJoinedOption
     * @return string[]
     */
    public function getNonStandardDeliveryOptions(bool $withJoinedOption = false): array
    {
        $nonStandardOptions = [
            InPostDeliveryOption::COD->name,
            InPostDeliveryOption::PWW->name,
        ];

        if ($withJoinedOption) {
            $nonStandardOptions[] = InPostDeliveryOption::CODPWW->name;
        }

        return $nonStandardOptions;
    }

    public function isFreeShippingEnabledForCarrier(string $code, string $method = '', ?int $storeId = null): bool
    {
        $configPattern = self::XML_PATH_FREE_SHIPPING_ENABLED_PATTERN;
        if (!empty($method)) {
            $methodCode = sprintf('%s/%s', $code, $method);
        } else {
            $methodCode = sprintf('%s', $code);
        }

        return $this->scopeConfig->isSetFlag(
            sprintf($configPattern, $methodCode),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFreeShippingSubtotalForCarrier(string $code, string $method = '', ?int $storeId = null): ?float
    {
        $configPattern = self::XML_PATH_FREE_SHIPPING_SUBTOTAL_PATTERN;
        if (!empty($method)) {
            $methodCode = sprintf('%s/%s', $code, $method);
        } else {
            $methodCode = sprintf('%s', $code);
        }

        $subtotalValue = $this->scopeConfig->getValue(
            sprintf($configPattern, $methodCode),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return is_scalar($subtotalValue) ? round((float)$subtotalValue, 2) : null;
    }

    public function getDeliveryDateDeadlineInDays(): int
    {
        $deadlineInDays = $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_DEADLINE_IN_DAYS);

        return is_scalar($deadlineInDays) ? (int)$deadlineInDays : self::DEFAULT_DELIVERY_DEADLINE;
    }

    public function isUsingCollectAddressTotalsForShippingEstimationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_USE_COLLECT_ADDRESS_TOTALS);
    }

    public function getDigitalDeliveryDateDeadlineInSeconds(?int $storeId = null): int
    {
        $deadlineInSeconds = $this->scopeConfig->getValue(
            self::XML_PATH_DIGITAL_DELIVERY_DEADLINE_IN_SEC,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return is_scalar($deadlineInSeconds) ? (int)$deadlineInSeconds : self::DEFAULT_DIGITAL_DELIVERY_DEADLINE;
    }
}
