<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Visibility implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => InPostPayCheckoutAgreementInterface::VISIBILITY_MAIN,
                'label' => __('Main Agreement')
            ],
            [
                'value' => InPostPayCheckoutAgreementInterface::VISIBILITY_CHILD,
                'label' => __('Sub-Agreement')
            ]
        ];
    }
}
