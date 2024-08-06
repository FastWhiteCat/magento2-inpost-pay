<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class TermsAndConditionsMappingConfigProvider
{
    private const XML_PATH_TERMS_AND_CONDITIONS_MAPPING = 'payment/inpost_pay/terms_and_conditions_mapping';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Returns mapped terms and conditions
     *
     * @return array
     */
    public function getTermsAndConditionsMapping(): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_TERMS_AND_CONDITIONS_MAPPING,
            ScopeInterface::SCOPE_STORE
        );

        $termsAndConditions = null;
        if (is_scalar($value)) {
            $termsAndConditions = $this->serializer->unserialize((string)$value);
        }

        return is_array($termsAndConditions) ? $termsAndConditions : [];
    }
}
