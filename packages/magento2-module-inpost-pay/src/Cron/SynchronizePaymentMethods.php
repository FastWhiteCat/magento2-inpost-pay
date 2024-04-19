<?php
declare(strict_types=1);

namespace InPost\InPostPay\Cron;

use InPost\InPostPay\Api\InPostPayAvailablePaymentMethodRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\PaymentMethods;

class SynchronizePaymentMethods
{
    public function __construct(
        private readonly InPostPayAvailablePaymentMethodRepositoryInterface $availablePaymentMethodRepository,
        private readonly PaymentMethods $paymentMethods
    ) {
    }

    public function execute() {
        $result = $this->paymentMethods->execute();

        if ($result['payment_type']) {
            $this->availablePaymentMethodRepository->deleteAll();
            $this->availablePaymentMethodRepository->insertMultiple($result['payment_type']);
        }
    }
}
