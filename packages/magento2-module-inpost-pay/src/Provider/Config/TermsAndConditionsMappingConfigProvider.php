<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class TermsAndConditionsMappingConfigProvider
{
    private const XML_PATH_TERMS_AND_CONDITIONS_MAPPING = 'payment/inpost_pay/terms_and_conditions_mapping';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Returns mapped terms and conditions
     *
     * @return mixed
     */
    public function getTermsAndConditionsMapping(): mixed
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_TERMS_AND_CONDITIONS_MAPPING)
            ? json_decode(
                (string)$this->scopeConfig->getValue(self::XML_PATH_TERMS_AND_CONDITIONS_MAPPING),
                true
            )
            : [];
    }
}
