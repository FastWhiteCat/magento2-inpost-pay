<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\CollectionFactory
    as InPostPayCheckoutAgreementCollectionFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\Collection as InPostPayCheckoutAgreementCollection;

class ValidateSubAgreementUsageInParentAgreementsEventObserver
{
    /**
     * @param InPostPayCheckoutAgreementCollectionFactory $inPostPayCheckoutAgreementCollectionFactory
     */
    public function __construct(
        private readonly InPostPayCheckoutAgreementCollectionFactory $inPostPayCheckoutAgreementCollectionFactory
    ) {
    }

    /**
     * @param InPostPayCheckoutAgreementInterface $agreement
     * @param bool $isDeleted
     * @return void
     * @throws LocalizedException
     */
    protected function validateSubAgreementUsageInParentAgreements(
        InPostPayCheckoutAgreementInterface $agreement,
        bool $isDeleted = false
    ): void {
        $canValidate = $isDeleted;
        $visibility = (int)$agreement->getVisibility();
        $agreementId = (int)$agreement->getAgreementId();

        if (!$agreement->isEnabled()) {
            $canValidate = true;
        }

        if ($visibility === InPostPayCheckoutAgreementInterface::VISIBILITY_CHILD
            && $agreementId !== 0
            && $canValidate
        ) {
            $parentAgreementIds = $this->findSubAgreementUsageInParentAgreementsByAgreementId(
                (int)$agreement->getAgreementId()
            );

            if (!empty($parentAgreementIds)) {
                $errorMsg = 'Main agreements with IDs: %1 are using this sub-agreement. '
                    . 'Please edit those Main Agreements first and remove usage of this sub-agreement.';

                throw new LocalizedException(__($errorMsg, implode(', ', $parentAgreementIds)));
            }
        }
    }

    /**
     * @param int $agreementId
     * @return array
     */
    private function findSubAgreementUsageInParentAgreementsByAgreementId(int $agreementId): array
    {
        $usedInParentAgreementIds = [];
        /** @var InPostPayCheckoutAgreementCollection $collection */
        $collection = $this->inPostPayCheckoutAgreementCollectionFactory->create();
        $collection->addFieldToFilter(
            InPostPayCheckoutAgreementInterface::VISIBILITY,
            ['eq' => InPostPayCheckoutAgreementInterface::VISIBILITY_MAIN]
        );

        foreach ($collection as $item) {
            /** @var InPostPayCheckoutAgreementInterface $item */
            $childrenIdsArray = explode(',', $item->getChildrenIds() ?? '');

            if (in_array($agreementId, $childrenIdsArray)) {
                $usedInParentAgreementIds[] = (int)$item->getAgreementId();
            }
        }

        return $usedInParentAgreementIds;
    }
}
