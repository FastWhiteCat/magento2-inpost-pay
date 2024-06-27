<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Block\Adminhtml\Form\Field\TermsAndConditionsField;
use InPost\InPostPay\Model\Cache\TermsAndConditions\Type as TermsAndConditionsCacheType;
use InPost\InPostPay\Provider\Config\TermsAndConditionsMappingConfigProvider;
use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;

class ConsentsProvider
{
    private const CONSENT_DESCRIPTION_MAX_LENGTH = 150;

    /**
     * @param TermsAndConditionsMappingConfigProvider $termsAndConditionsMappingConfigProvider
     * @param CheckoutAgreementsListInterface $checkoutAgreementsList
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly TermsAndConditionsMappingConfigProvider $termsAndConditionsMappingConfigProvider,
        private readonly CheckoutAgreementsListInterface $checkoutAgreementsList,
        private readonly FilterBuilder $filterBuilder,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository,
        private readonly SerializerInterface $serializer,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @return array
     */
    public function getConsents(): array
    {
        $consents = $this->cache->load(TermsAndConditionsCacheType::TYPE_IDENTIFIER);

        if (empty($consents)) {
            $termsAndConditionsMapping = $this->termsAndConditionsMappingConfigProvider->getTermsAndConditionsMapping();

            if (!$termsAndConditionsMapping) {
                return [];
            }

            $ids = array_column($termsAndConditionsMapping, TermsAndConditionsField::MAGENTO_AGREEMENT_ID_FIELD);

            $checkoutAgreementsArray = $this->getCheckoutAgreementsList($ids);
            $checkoutAgreementsVersion = $this->getCheckoutAgreementsVersion($ids);

            $consents = [];
            foreach ($termsAndConditionsMapping as $item) {
                $additionalConsentLinks = [];

                foreach ($item[TermsAndConditionsField::ADDITIONAL_LINKS_FIELD] ?? [] as $additionalConsentLink) {
                    $additionalConsentLinks[] = [
                        'consent_id' => $additionalConsentLink[TermsAndConditionsField::MAGENTO_AGREEMENT_ID_FIELD],
                        'consent_link' => $additionalConsentLink[TermsAndConditionsField::AGREEMENT_URL_FIELD],
                        'label_link' => $additionalConsentLink[TermsAndConditionsField::LINK_LABEL_FIELD] ?? null,
                    ];
                }

                $consents[] = [
                    'consent_id' => $item[TermsAndConditionsField::MAGENTO_AGREEMENT_ID_FIELD],
                    'consent_link' => $item[TermsAndConditionsField::AGREEMENT_URL_FIELD],
                    'label_link' => $item[TermsAndConditionsField::LINK_LABEL_FIELD] ?? null,
                    'additional_consent_links' => $additionalConsentLinks,
                    'consent_description' => substr(
                        $checkoutAgreementsArray[$item[TermsAndConditionsField::MAGENTO_AGREEMENT_ID_FIELD]]['name'],
                        0,
                        self::CONSENT_DESCRIPTION_MAX_LENGTH
                    ),
                    'consent_version' => $checkoutAgreementsVersion[
                        $item[TermsAndConditionsField::MAGENTO_AGREEMENT_ID_FIELD]
                    ],
                    'requirement_type' => $item[TermsAndConditionsField::REQUIREMENT_FIELD]
                ];
            }

            $encodedConsentsData = (string)$this->serializer->serialize($consents);

            $this->cache->save(
                $encodedConsentsData,
                TermsAndConditionsCacheType::TYPE_IDENTIFIER,
                [TermsAndConditionsCacheType::CACHE_TAG],
                TermsAndConditionsCacheType::TTL
            );
        }

        if (empty($consents)) {
            return [];
        }

        return is_array($consents) ? $consents : $this->serializer->unserialize($consents);
    }

    /**
     * @param array $ids
     * @return array
     */
    private function getCheckoutAgreementsList(array $ids): array
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('agreement_id')
                    ->setValue($ids)
                    ->setConditionType('in')
                    ->create()
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $checkoutAgreementsList = $this->checkoutAgreementsList->getList($searchCriteria);

        $checkoutAgreementsArray = [];
        foreach ($checkoutAgreementsList as $agreement) {
            $checkoutAgreementsArray[$agreement->getAgreementId()] = $agreement->getData();
        }

        return $checkoutAgreementsArray;
    }

    /**
     * @param array $ids
     * @return array
     */
    private function getCheckoutAgreementsVersion(array $ids): array
    {
        return $this->checkoutAgreementsVersionRepository->getList($ids);
    }
}
