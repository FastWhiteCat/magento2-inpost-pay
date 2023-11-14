<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Provider\Config\TermsAndConditionsMappingConfigProvider;
use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ConsentsProvider
{
    private const CONSENT_DESCRIPTION_MAX_LENGTH = 150;

    public const MAGENTO_AGREEMENT_ID_FIELD = 'magento_agreement_id';

    /**
     * @param TermsAndConditionsMappingConfigProvider $termsAndConditionsMappingConfigProvider
     * @param CheckoutAgreementsListInterface $checkoutAgreementsList
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
     */
    public function __construct(
        private readonly TermsAndConditionsMappingConfigProvider $termsAndConditionsMappingConfigProvider,
        private readonly CheckoutAgreementsListInterface $checkoutAgreementsList,
        private readonly FilterBuilder $filterBuilder,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
    ) {
    }

    /**
     * @return array
     */
    public function getConsents(): array
    {
        $termsAndConditionsMapping = $this->termsAndConditionsMappingConfigProvider->getTermsAndConditionsMapping();

        if (!$termsAndConditionsMapping) {
            return [];
        }

        $ids = array_column($termsAndConditionsMapping, self::MAGENTO_AGREEMENT_ID_FIELD);

        $checkoutAgreementsArray = $this->getCheckoutAgreementsList($ids);
        $checkoutAgreementsVersion = $this->getCheckoutAgreementsVersion($ids);

        $consents = [];
        foreach ($termsAndConditionsMapping as $item) {
            $consents[] = [
                'consent_id' => $item[self::MAGENTO_AGREEMENT_ID_FIELD],
                'consent_link' => $item['agreement_url'],
                'consent_description' => substr(
                    $checkoutAgreementsArray[$item[self::MAGENTO_AGREEMENT_ID_FIELD]]['name'],
                    0,
                    self::CONSENT_DESCRIPTION_MAX_LENGTH
                ),
                'consent_version' => $checkoutAgreementsVersion[$item[self::MAGENTO_AGREEMENT_ID_FIELD]],
                'requirement_type' => $item['requirement']
            ];
        }

        return $consents;
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
