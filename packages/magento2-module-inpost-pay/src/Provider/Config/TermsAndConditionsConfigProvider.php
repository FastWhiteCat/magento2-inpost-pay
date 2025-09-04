<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Block\Adminhtml\Form\Field\TermsAndConditionsField as AgreementFields;
use InPost\InPostPay\Model\Config\Source\TermsAndConditionsRequirements;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class TermsAndConditionsConfigProvider
{
    private const XML_PATH_LEGACY_MAPPING_ENABLED = 'payment/inpost_pay/use_legacy_terms_and_conditions_mapping';
    private const XML_PATH_TERMS_AND_CONDITIONS_MAPPING = 'payment/inpost_pay/terms_and_conditions_new_mapping';

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
     * @return bool
     */
    public function isLegacyMappingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LEGACY_MAPPING_ENABLED);
    }

    /**
     * Returns mapped terms and conditions
     *
     * @param int|null $storeId
     * @return array
     */
    public function getTermsAndConditionsMapping(?int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_TERMS_AND_CONDITIONS_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $termsAndConditions = null;
        if (is_scalar($value)) {
            $termsAndConditions = $this->serializer->unserialize((string)$value);
        }

        return $this->aggregateAdditionalConsentLinks(is_array($termsAndConditions) ? $termsAndConditions : []);
    }

    /**
     * @param array $termsAndConditions
     * @return array
     */
    private function aggregateAdditionalConsentLinks(array $termsAndConditions): array
    {
        $mainTermsAndConditions = [];

        foreach ($termsAndConditions as $termsAndCondition) {
            $parentAgreementId = (int)$termsAndCondition[AgreementFields::PARENT_MAGENTO_AGREEMENT_ID_FIELD];

            if ($parentAgreementId) {
                continue;
            }

            $agreementId = (int)($termsAndCondition[AgreementFields::MAGENTO_AGREEMENT_ID_FIELD] ?? 0);
            $requirementType = $termsAndCondition[AgreementFields::REQUIREMENT_FIELD]
                ?? TermsAndConditionsRequirements::OPTIONAL;
            $agreementUrl = $termsAndCondition[AgreementFields::AGREEMENT_URL_FIELD] ?? '';
            $linkLabel = $termsAndCondition[AgreementFields::AGREEMENT_NAME_FIELD] ?? '';

            $mainTermsAndConditions[$agreementId] = [
                AgreementFields::MAGENTO_AGREEMENT_ID_FIELD => $agreementId,
                AgreementFields::REQUIREMENT_FIELD => $requirementType,
                AgreementFields::AGREEMENT_URL_FIELD => $agreementUrl,
                AgreementFields::LINK_LABEL_FIELD => $linkLabel,
            ];
        }

        foreach ($termsAndConditions as $termsAndCondition) {
            $parentId = (int)$termsAndCondition[AgreementFields::PARENT_MAGENTO_AGREEMENT_ID_FIELD];

            if (empty($parentId) || !isset($mainTermsAndConditions[$parentId])) {
                continue;
            }

            $agreementId = (int)($termsAndCondition[AgreementFields::MAGENTO_AGREEMENT_ID_FIELD] ?? 0);
            $agreementUrl = $termsAndCondition[AgreementFields::AGREEMENT_URL_FIELD] ?? '';
            $linkLabel = $termsAndCondition[AgreementFields::AGREEMENT_NAME_FIELD] ?? '';

            $additionalConsentLink = [
                AgreementFields::MAGENTO_AGREEMENT_ID_FIELD => $agreementId,
                AgreementFields::AGREEMENT_URL_FIELD => $agreementUrl,
                AgreementFields::LINK_LABEL_FIELD => $linkLabel,
            ];

            $mainTermsAndConditions[$parentId][AgreementFields::ADDITIONAL_LINKS_FIELD][] = $additionalConsentLink;
        }

        return $mainTermsAndConditions;
    }
}
