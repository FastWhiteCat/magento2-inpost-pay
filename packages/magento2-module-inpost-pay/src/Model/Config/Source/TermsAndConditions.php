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
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        $agreementCollection = $this->agreementCollectionFactory->create();
        $agreementCollection->addFieldToFilter('is_active', ['eq' => 1]);

        /** @var Agreement $agreement */
        foreach ($agreementCollection as $agreement) {
            $result[] = ['label' =>  $agreement->getName(), 'value' => $agreement->getAgreementId()];
        }

        return $result;
    }
}
