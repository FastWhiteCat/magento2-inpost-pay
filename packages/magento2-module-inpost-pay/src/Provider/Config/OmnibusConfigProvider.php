<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Model\Config\Source\ProductAttributes;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class OmnibusConfigProvider
{
    private const XML_PATH_OMNIBUS_SALESRULES = 'payment/inpost_pay/omnibus_salesrules';
    private const XML_PATH_OMNIBUS_LOWEST_PRICE_ATTR = 'payment/inpost_pay/omnibus_lowest_price_product_attribute';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * @return int[]
     */
    public function getOmnibusCartPriceRuleIds(): array
    {
        $configValue = $this->scopeConfig->getValue(
            self::XML_PATH_OMNIBUS_SALESRULES,
            ScopeInterface::SCOPE_WEBSITE
        );

        $ruleIdsCombined = explode(',', is_scalar($configValue) ? (string)$configValue : '');
        $ruleIds = [];

        if ($ruleIdsCombined) {
            foreach ($ruleIdsCombined as $ruleId) {
                $ruleIds[] = (int)$ruleId;
            }
        }

        return $ruleIds;
    }

    /**
     * @return string|null
     */
    public function getOmnibusProductLowestPriceAttributeCode(): ?string
    {
        $configValue = $this->scopeConfig->getValue(
            self::XML_PATH_OMNIBUS_LOWEST_PRICE_ATTR,
            ScopeInterface::SCOPE_STORE
        );

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
