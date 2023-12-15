<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Enum\InPostDeliveryOption;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ShipmentMappingConfigProvider
{
    public const DEFAULT_DELIVERY_DEADLINE = 7;

    public const OPTION_STANDARD = 'STANDARD';
    private const XML_PATH_DELIVERY_MAPPING_PATTERN = 'payment/inpost_pay/inpost_%s_%s_mapping';
    private const XML_PATH_DELIVERY_DEADLINE_IN_DAYS = 'payment/inpost_pay/delivery_deadline_in_days';
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
     * @return string
     * @throws InPostPayInvalidConfigurationException
     */
    public function getCarrierMethodCodeForOptions(string $deliveryType, string $option): string
    {
        $carrierConfigPattern = self::XML_PATH_DELIVERY_MAPPING_PATTERN;
        $carrierConfigPath = sprintf($carrierConfigPattern, strtolower($deliveryType), strtolower($option));
        $carrier = $this->scopeConfig->getValue($carrierConfigPath, ScopeInterface::SCOPE_WEBSITE);

        if (empty($carrier) || !is_scalar($carrier)) {
            throw new InPostPayInvalidConfigurationException(
                __('InPost Courier not mapped for delivery type: %1 with option: %2', $deliveryType, $option)
            );
        }

        return (string)$carrier;
    }

    public function getAllDeliveryTypes(): array
    {
        return [InPostDeliveryType::APM->name, InPostDeliveryType::COURIER->name];
    }

    public function getNonStandardDeliveryOptions(): array
    {
        return [
            InPostDeliveryOption::COD->name,
            InPostDeliveryOption::PWW->name,
            InPostDeliveryOption::CODPWW->name
        ];
    }

    public function isFreeShippingEnabledForCarrier(string $code, string $method = ''): bool
    {
        $configPattern = self::XML_PATH_FREE_SHIPPING_ENABLED_PATTERN;
        if (!empty($method)) {
            $methodCode = sprintf('%s/%s', $code, $method);
        } else {
            $methodCode = sprintf('%s', $code);
        }

        return $this->scopeConfig->isSetFlag(sprintf($configPattern, $methodCode));
    }

    public function getFreeShippingSubtotalForCarrier(string $code, string $method = ''): ?float
    {
        $configPattern = self::XML_PATH_FREE_SHIPPING_SUBTOTAL_PATTERN;
        if (!empty($method)) {
            $methodCode = sprintf('%s/%s', $code, $method);
        } else {
            $methodCode = sprintf('%s', $code);
        }

        $subtotalValue = $this->scopeConfig->getValue(
            sprintf($configPattern, $methodCode),
            ScopeInterface::SCOPE_WEBSITE
        );

        return is_scalar($subtotalValue) ? round((float)$subtotalValue, 2) : null;
    }

    public function getDeliveryDateDeadlineInDays(): int
    {
        $deadlineInDays = $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_DEADLINE_IN_DAYS);

        return is_scalar($deadlineInDays) ? (int)$deadlineInDays : self::DEFAULT_DELIVERY_DEADLINE;
    }
}
