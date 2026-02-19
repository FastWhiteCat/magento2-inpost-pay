<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use InPost\InPostPay\Api\InPostPayAvailablePaymentMethodRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

class AcceptedPaymentTypes implements OptionSourceInterface
{
    public function __construct(
        private readonly InPostPayAvailablePaymentMethodRepositoryInterface $inPostPayAvailablePaymentMethodRepository
    ) {
    }

    public function toOptionArray(): array
    {
        $paymentMethods = $this->inPostPayAvailablePaymentMethodRepository->getAllValuesAsArray();
        $result = [];

        foreach ($paymentMethods as $paymentMethod) {
            $result[] = [
                'value' => $paymentMethod['payment_code'],
                'label' => $paymentMethod['payment_code']
            ];
        }

        return $result;
    }
}
