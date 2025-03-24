<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Framework\Data\OptionSourceInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\CollectionFactory as AgreementCollectionFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\Collection as AgreementCollection;

class SubAgreements implements OptionSourceInterface
{
    /**
     * @param AgreementCollectionFactory $agreementCollectionFactory
     */
    public function __construct(
        private readonly AgreementCollectionFactory $agreementCollectionFactory
    ) {
    }

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        /** @var AgreementCollection $agreementCollection */
        $agreementCollection = $this->agreementCollectionFactory->create();
        $agreementCollection->addFieldToFilter(
            InPostPayCheckoutAgreementInterface::VISIBILITY,
            ['eq' => InPostPayCheckoutAgreementInterface::VISIBILITY_CHILD]
        );
        $agreementCollection->addFieldToFilter(InPostPayCheckoutAgreementInterface::IS_ENABLED, ['eq' => 1]);
        $options = [];

        foreach ($agreementCollection->getItems() as $item) {
            if ($item instanceof InPostPayCheckoutAgreementInterface) {
                $options[] = [
                    'value' => (int)$item->getAgreementId(),
                    'label' => sprintf('{%s|%s}', $item->getAgreementId(), $item->getUrlLabel())
                ];
            }
        }

        return $options;
    }
}
