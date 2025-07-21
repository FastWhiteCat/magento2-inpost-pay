<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\SalesRule\Model\Data\Rule;
use Magento\SalesRule\Model\Rule as RuleModel;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as SalesRuleCollectionFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as SalesRuleCollection;

class SalesRules implements OptionSourceInterface
{
    /**
     * @param SalesRuleCollectionFactory $salesRuleCollectionFactory
     */
    public function __construct(
        private readonly SalesRuleCollectionFactory $salesRuleCollectionFactory,
    ) {
    }

    /**
     * @return array[]
     */
    public function toOptionArray(bool $onlySpecificCoupon = false): array
    {
        $salesRules = $this->getSalesRules($onlySpecificCoupon);
        $salesRulesOptions = [];

        foreach ($salesRules as $salesRule) {
            $label = (string)$salesRule->getName();
            $value = (int)$salesRule->getRuleId();
            $salesRulesOptions[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $salesRulesOptions;
    }

    /**
     * @return array
     */
    private function getSalesRules(bool $onlySpecificCoupon = false): array
    {
        /** @var SalesRuleCollection $salesRuleCollection */
        $salesRuleCollection = $this->salesRuleCollectionFactory->create();
        if ($onlySpecificCoupon) {
            $salesRuleCollection->addFieldToFilter(
                Rule::KEY_COUPON_TYPE,
                ['eq' => RuleModel::COUPON_TYPE_SPECIFIC]
            );
        } else {
            $salesRuleCollection->addFieldToFilter(
                Rule::KEY_COUPON_TYPE,
                ['neq' => RuleModel::COUPON_TYPE_NO_COUPON]
            );
        }

        $salesRuleCollection->addOrder(Rule::KEY_RULE_ID, Collection::SORT_ORDER_ASC);
        $salesRules = [];

        foreach ($salesRuleCollection->getItems() as $salesRule) {
            if ($salesRule instanceof RuleModel) {
                $salesRules[] = $salesRule;
            }
        }

        return $salesRules;
    }
}
