<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\CheckoutAgreements\Model\Agreement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;

class TermsAndConditions implements OptionSourceInterface
{
    /**
     * @param CollectionFactory $agreementCollectionFactory
     */
    public function __construct(private readonly CollectionFactory $agreementCollectionFactory)
    {
    }

    /**
     * @param bool $withNone
     * @return array
     */
    public function toOptionArray(bool $withNone = false): array
    {
        $result = [];
        $agreementCollection = $this->agreementCollectionFactory->create();
        $agreementCollection->addFieldToFilter('is_active', ['eq' => 1]);

        if ($withNone) {
            $result[] = ['label' =>  __('None'), 'value' => 0];
        }

        /** @var Agreement $agreement */
        foreach ($agreementCollection as $agreement) {
            $result[] = ['label' =>  $agreement->getName(), 'value' => $agreement->getAgreementId()];
        }

        return $result;
    }
}
