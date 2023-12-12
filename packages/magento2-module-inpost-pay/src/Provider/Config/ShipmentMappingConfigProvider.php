<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ShipmentMappingConfigProvider
{
    public const DEFAULT_DELIVERY_DEADLINE = 7;

    private const XML_PATH_DELIVERY_MAPPING_FOR_INPOST_COURIER = 'payment/inpost_pay/inpost_courier_mapping';
    private const XML_PATH_DELIVERY_MAPPING_FOR_INPOST_PICKUP = 'payment/inpost_pay/inpost_pickup_mapping';
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
     * Returns carrier method code mapped to InPost Courier
     *
     * @return string
     * @throws InPostPayInvalidConfigurationException
     */
    public function getCarrierMethodCodeForInPostCourier(): string
    {
        $carrier = $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_MAPPING_FOR_INPOST_COURIER,
            ScopeInterface::SCOPE_WEBSITE);

        if (empty($carrier) || !is_scalar($carrier)) {
            throw new InPostPayInvalidConfigurationException(__('InPost Courier not mapped'));
        }

        return (string)$carrier;
    }

    /**
     * Returns carrier method code mapped to InPost Pickup
     *
     * @return string
     * @throws InPostPayInvalidConfigurationException
     */
    public function getCarrierMethodCodeForInPostPickup(): string
    {
        $carrier = $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_MAPPING_FOR_INPOST_PICKUP,
            ScopeInterface::SCOPE_WEBSITE);

        if (empty($carrier) || !is_scalar($carrier)) {
            throw new InPostPayInvalidConfigurationException(__('InPost Paczkomat 24/7 not mapped'));
        }

        return (string)$carrier;
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

        $subtotalValue = $this->scopeConfig->getValue(sprintf($configPattern, $methodCode),
            ScopeInterface::SCOPE_WEBSITE);

        return is_scalar($subtotalValue) ? round((float)$subtotalValue, 2) : null;
    }

    public function getDeliveryDateDeadlineInDays(): int
    {
        $deadlineInDays = $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_DEADLINE_IN_DAYS);

        return is_scalar($deadlineInDays) ? (int)$deadlineInDays : self::DEFAULT_DELIVERY_DEADLINE;
    }
}
