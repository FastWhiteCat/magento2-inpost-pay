<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Model\InPostPayCheckoutAgreement;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement as InPostPayCheckoutAgreementResource;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayCheckoutAgreementInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayCheckoutAgreementInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayCheckoutAgreementInterface::AGREEMENT_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayCheckoutAgreement::class, InPostPayCheckoutAgreementResource::class);
    }

    /**
     * Adds a requirement_sort_order column based on values of the requirement column
     * and sorts the collection by the new column in ascending order.
     *
     * @return $this
     */
    public function addSortingByRequirement(): self
    {
        $connection = $this->getConnection();
        $requirementCase = $connection->getCaseSql(
            'main_table.requirement',
            [
                sprintf('\'%s\'', InPostPayCheckoutAgreementInterface::REQUIREMENT_REQUIRED_ALWAYS) => '1',
                sprintf('\'%s\'', InPostPayCheckoutAgreementInterface::REQUIREMENT_REQUIRED_ONCE) => '2',
                sprintf('\'%s\'', InPostPayCheckoutAgreementInterface::REQUIREMENT_OPTIONAL) => '3',
            ],
            '99'
        );

        $this->getSelect()
            ->columns(['requirement_sort_order' => $requirementCase])
            ->order('requirement_sort_order ASC');

        return $this;
    }

    /**
     * Adds visibility filters to the query to ensure only enabled and main visibility agreements are included.
     *
     * @return $this
     */
    public function addVisibilityFilter(): self
    {
        $this->addFieldToFilter(InPostPayCheckoutAgreementInterface::IS_ENABLED, ['eq' => 1]);
        $this->addFieldToFilter(
            InPostPayCheckoutAgreementInterface::VISIBILITY,
            ['eq' => InPostPayCheckoutAgreementInterface::VISIBILITY_MAIN]
        );

        return $this;
    }
}
