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
                'label' => __('Courier')->render(),
                'value' => RestrictionsRuleInterface::APPLIES_TO_COURIER,
            ],
            [
                'label' => __('APM')->render(),
                'value' => RestrictionsRuleInterface::APPLIES_TO_APM,
            ],
            [
                'label' => __('Courier & APM')->render(),
                'value' => RestrictionsRuleInterface::APPLIES_TO_BOTH,
            ]
        ];
    }
}
