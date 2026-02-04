<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Block\Adminhtml\Form\Field\TermsAndConditionsField;
use InPost\InPostPay\Model\Cache\TermsAndConditions\Type as TermsAndConditionsCacheType;
use InPost\InPostPay\Model\Config\Source\TermsAndConditionsRequirements;
use InPost\InPostPay\Provider\Config\TermsAndConditionsConfigProvider;
use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;

class LegacyConsentsProvider
{
    private const CONSENT_DESCRIPTION_MAX_LENGTH = 150;
    private const CONSENT_LIMIT = 10;
    private const SORT_ORDER = [
        TermsAndConditionsRequirements::ALWAYS => 1,
        TermsAndConditionsRequirements::ONLY_IN_NEW_VERSION => 2,
        TermsAndConditionsRequirements::OPTIONAL => 3,
        TermsAndConditionsRequirements::ADDITIONAL_LINK => 4
    ];

    /**
     * @param TermsAndConditionsConfigProvider $termsAndConditionsMappingConfigProvider
     * @param CheckoutAgreementsListInterface $checkoutAgreementsList
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly TermsAndConditionsConfigProvider $termsAndConditionsMappingConfigProvider,
        private readonly CheckoutAgreementsListInterface $checkoutAgreementsList,
        private readonly FilterBuilder $filterBuilder,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository,
        private readonly SerializerInterface $serializer,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getConsents(int $storeId): array
    {
        $cacheIdentifier = sprintf('%s_%s', TermsAndConditionsCacheType::TYPE_IDENTIFIER, $storeId);
        $consents = $this->cache->load($cacheIdentifier);

        if (empty($consents)) {
            $termsAndConditionsMapping = $this->termsAndConditionsMappingConfigProvider
                ->getTermsAndConditionsMapping($storeId);

            if (!$termsAndConditionsMapping) {
                return [];
            }

            $ids = array_column($termsAndConditionsMapping, TermsAndConditionsField::MAGENTO_AGREEMENT_ID_FIELD);

            $checkoutAgreementsArray = $this->getCheckoutAgreementsList($ids);
            $checkoutAgreementsVersion = $this->getCheckoutAgreementsVersion($ids);
            $termsAndConditionsMapping = $this->sortTermsAndConditions($termsAndConditionsMapping);

            $consents = [];
            $i = 0;
            foreach ($termsAndConditionsMapping as $item) {
                $i++;
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
                        ] ?? '1',
                    'requirement_type' => $item[TermsAndConditionsField::REQUIREMENT_FIELD]
                ];

                if ($i >= self::CONSENT_LIMIT) {
                    break;
                }
            }

            $encodedConsentsData = (string)$this->serializer->serialize($consents);

            $this->cache->save(
                $encodedConsentsData,
                $cacheIdentifier,
                [TermsAndConditionsCacheType::CACHE_TAG],
                TermsAndConditionsCacheType::TTL
            );
        }

        /** @phpstan-ignore-next-line */
        return is_array($consents) ? $consents : (array)$this->serializer->unserialize($consents);
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

    /**
     * @param array $termsAndConditionsMapping
     * @return array
     */
    private function sortTermsAndConditions(array $termsAndConditionsMapping): array
    {
        $termsAndConditions = [];
        foreach ($termsAndConditionsMapping as $key => $item) {
            $termsAndConditions[$key] = self::SORT_ORDER[$item[TermsAndConditionsField::REQUIREMENT_FIELD]];
        }
        asort($termsAndConditions);

        $sortedTermsAndConditions = [];
        foreach ($termsAndConditions as $key => $item) {
            $sortedTermsAndConditions[] = $termsAndConditionsMapping[$key];
        }

        return $sortedTermsAndConditions;
    }
}
