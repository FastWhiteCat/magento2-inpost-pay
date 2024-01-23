<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\Source\RestrictionsRule;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\Data\OptionSourceInterface;

class AppliesTo implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('InPost Delivery')->render(),
                'value' => RestrictionsRuleInterface::APPLIES_TO_DELIVERY,
            ],
            [
                'label' => __('InPost Pay')->render(),
                'value' => RestrictionsRuleInterface::APPLIES_TO_PAYMENT,
            ],
            [
                'label' => __('InPost Delivery & InPost Pay')->render(),
                'value' => RestrictionsRuleInterface::APPLIES_TO_BOTH,
            ]
        ];
    }
}
