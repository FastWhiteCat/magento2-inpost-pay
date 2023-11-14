<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ShipmentMappingConfigProvider
{
    private const XML_PATH_DELIVERY_MAPPING_FOR_INPOST_COURIER = 'payment/inpost_pay/inpost_courier_mapping';
    private const XML_PATH_DELIVERY_MAPPING_FOR_INPOST_PICKUP = 'payment/inpost_pay/inpost_pickup_mapping';

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
        $carrier = $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_MAPPING_FOR_INPOST_COURIER);

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
        $carrier = $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_MAPPING_FOR_INPOST_PICKUP);

        if (empty($carrier) || !is_scalar($carrier)) {
            throw new InPostPayInvalidConfigurationException(__('InPost Paczkomat 24/7 not mapped'));
        }

        return (string)$carrier;
    }
}
