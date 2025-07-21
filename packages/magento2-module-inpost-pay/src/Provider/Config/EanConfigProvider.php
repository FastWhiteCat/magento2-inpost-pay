<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Model\Config\Source\ProductAttributes;
use Magento\Framework\App\Config\ScopeConfigInterface;

class EanConfigProvider
{
    private const XML_PATH_EAN_ATTR = 'payment/inpost_pay/ean_product_attribute';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return string|null
     */
    public function getProductEanAttributeCode(): ?string
    {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_EAN_ATTR);

        if (empty($configValue)
            || !is_scalar($configValue)
            || $configValue === ProductAttributes::NOT_SELECTED_ATTRIBUTE_VALUE
        ) {
            $attributeCode = null;
        } else {
            $attributeCode = (string)$configValue;
        }

        return $attributeCode;
    }
}
