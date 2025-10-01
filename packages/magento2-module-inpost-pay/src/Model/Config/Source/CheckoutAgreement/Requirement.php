<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Requirement implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => InPostPayCheckoutAgreementInterface::REQUIREMENT_OPTIONAL,
                'label' => __('Optional')
            ],
            [
                'value' => InPostPayCheckoutAgreementInterface::REQUIREMENT_REQUIRED_ONCE,
                'label' => __('Required once')
            ],
            [
                'value' => InPostPayCheckoutAgreementInterface::REQUIREMENT_REQUIRED_ALWAYS,
                'label' => __('Required always')
            ]
        ];
    }
}
