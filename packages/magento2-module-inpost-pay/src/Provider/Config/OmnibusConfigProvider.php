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
    private const ENABLE_CUSTOM_PROMO_PRICE = 'enable_custom_promo_price_for_customer_group';
    private const CUSTOM_PROMO_PRICE_ATTRIBUTE = 'custom_promo_price_attribute_for_customer_group';
    private const CUSTOM_PROMO_PRICE_CUSTOMER_GROUPS = 'custom_promo_price_customer_groups';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * @param int|null $storeId
     * @return int[]
     */
    public function getOmnibusCartPriceRuleIds(?int $storeId = null): array
    {
        $configValue = $this->scopeConfig->getValue(
            self::XML_PATH_OMNIBUS_SALESRULES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $ruleIdsCombined = explode(',', is_scalar($configValue) ? (string)$configValue : '');
        $ruleIds = [];

        foreach ($ruleIdsCombined as $ruleId) {
            $ruleIds[] = (int)$ruleId;
        }

        return $ruleIds;
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getOmnibusProductLowestPriceAttributeCode(?int $storeId = null): ?string
    {
        $configValue = $this->scopeConfig->getValue(
            self::XML_PATH_OMNIBUS_LOWEST_PRICE_ATTR,
            ScopeInterface::SCOPE_STORE,
            $storeId
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

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCustomPromoPriceForSpecificCustomerGroupEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            sprintf('payment/inpost_pay/%s', self::ENABLE_CUSTOM_PROMO_PRICE),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getCustomProductPromoPriceAttributeCode(?int $storeId = null): ?string
    {
        $configValue = $this->scopeConfig->getValue(
            sprintf('payment/inpost_pay/%s', self::CUSTOM_PROMO_PRICE_ATTRIBUTE),
            ScopeInterface::SCOPE_STORE,
            $storeId
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

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getCustomProductPromoPriceCustomerGroups(?int $storeId = null): array
    {
        $configValue = $this->scopeConfig->getValue(
            sprintf('payment/inpost_pay/%s', self::CUSTOM_PROMO_PRICE_CUSTOMER_GROUPS),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $customerGroups = [];

        if (is_scalar($configValue)) {
            foreach (explode(',', (string)$configValue) as $customerGroupId) {
                $customerGroups[] = (int)$customerGroupId;
            }
        }

        return $customerGroups;
    }
}
