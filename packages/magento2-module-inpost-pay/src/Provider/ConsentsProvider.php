<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Model\Cache\TermsAndConditions\Type as TermsAndConditionsCacheType;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\CollectionFactory
    as InPostPayCheckoutAgreementCollectionFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\Collection
    as InPostPayCheckoutAgreementCollection;

class ConsentsProvider
{
    private const CONSENT_DESCRIPTION_MAX_LENGTH = 150;
    private const CONSENT_LIMIT = 10;

    /**
     * @param InPostPayCheckoutAgreementCollectionFactory $inPostPayCheckoutAgreementCollectionFactory
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly InPostPayCheckoutAgreementCollectionFactory $inPostPayCheckoutAgreementCollectionFactory,
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
            $agreements = $this->getAgreementsByStoreId($storeId);
            $consents = [];

            foreach ($agreements as $agreement) {
                $childAgreements = $agreement->getChildrenAgreements() ?? [];
                $additionalConsentLinks = [];

                foreach ($childAgreements as $childAgreement) {
                    /** @var InPostPayCheckoutAgreementInterface $childAgreement */
                    $additionalConsentLinks[] = [
                        'consent_id' => (int)$childAgreement->getAgreementId(),
                        'consent_link' => $childAgreement->getAgreementUrl(),
                        'label_link' => $this->getLabelLinkForAdditionalAgreement($agreement, $childAgreement),
                    ];
                }

                $consents[] = [
                    'consent_id' => (int)$agreement->getAgreementId(),
                    'consent_link' => $agreement->getAgreementUrl(),
                    'label_link' => $agreement->getUrlLabel(),
                    'additional_consent_links' => $additionalConsentLinks,
                    'consent_description' => $this->prepareMainAgreementCheckboxText($agreement),
                    'consent_version' => $agreement->getVersion(),
                    'requirement_type' => $agreement->getRequirement()
                ];
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
     * @param int $storeId
     * @return InPostPayCheckoutAgreementInterface[]
     */
    public function getAgreementsByStoreId(int $storeId): array
    {
        /** @var InPostPayCheckoutAgreementCollection $collection */
        $collection = $this->inPostPayCheckoutAgreementCollectionFactory->create();
        $collection->addSortingByRequirement();
        $collection->addVisibilityFilter();
        $agreements = [];

        foreach ($collection->getItems() as $agreement) {
            if (!$agreement instanceof InPostPayCheckoutAgreementInterface) {
                continue;
            }

            $agreementStoreIds = $agreement->getStoreIds();

            if (in_array($storeId, $agreementStoreIds) || in_array(0, $agreementStoreIds)) {
                $agreements[] = $agreement;
            }
        }

        return array_slice($agreements, 0, self::CONSENT_LIMIT);
    }

    /**
     * @param InPostPayCheckoutAgreementInterface $agreement
     * @param InPostPayCheckoutAgreementInterface $childAgreement
     * @return string
     */
    private function getLabelLinkForAdditionalAgreement(
        InPostPayCheckoutAgreementInterface $agreement,
        InPostPayCheckoutAgreementInterface $childAgreement
    ): string {
        $childAgreementId = (int)$childAgreement->getAgreementId();
        $mainAgreementCheckboxText = $agreement->getCheckboxText();
        $pattern = '/\{' . $childAgreementId . '\|([^}]+)\}/';
        $foundLabel = '';

        if (preg_match($pattern, $mainAgreementCheckboxText, $matches)) {
            $foundLabel = $matches[1];
        }

        return !empty($foundLabel) ? $foundLabel : $childAgreement->getUrlLabel();
    }

    /**
     * @param InPostPayCheckoutAgreementInterface $agreement
     * @return string
     */
    private function prepareMainAgreementCheckboxText(InPostPayCheckoutAgreementInterface $agreement): string
    {
        $childrenIds = $agreement->getChildrenIds();
        $mainAgreementCheckboxText = $agreement->getCheckboxText();
        $mainAgreementCheckboxText = str_replace(
            InPostPayCheckoutAgreementInterface::URL_LABEL_PATTERN,
            "#" . (int)$agreement->getAgreementId(),
            $mainAgreementCheckboxText
        );

        if (empty($childrenIds)) {
            return (string)$mainAgreementCheckboxText;
        }

        $childrenIdsArray = explode(',', $childrenIds);

        foreach ($childrenIdsArray as $childAgreementId) {
            $childAgreementId = (int)$childAgreementId;
            $pattern = '/\{' . $childAgreementId . '\|([^}]+)\}/';
            $replacement = "#" . $childAgreementId;
            $mainAgreementCheckboxText = preg_replace($pattern, $replacement, (string)$mainAgreementCheckboxText);
        }

        return $this->limitString((string)$mainAgreementCheckboxText);
    }

    private function limitString(string $text): string
    {
        $ellipsis = '...';

        if (mb_strlen($text, 'UTF-8') > self::CONSENT_DESCRIPTION_MAX_LENGTH) {
            $ellipsisLength = mb_strlen($ellipsis, 'UTF-8');

            return mb_substr($text, 0, self::CONSENT_DESCRIPTION_MAX_LENGTH - $ellipsisLength, 'UTF-8') . $ellipsis;
        }

        return $text;
    }
}
