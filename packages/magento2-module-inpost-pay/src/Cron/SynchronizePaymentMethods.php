<?php
declare(strict_types=1);

namespace InPost\InPostPay\Cron;

use InPost\InPostPay\Service\SynchronizePaymentMethods as SynchronizePaymentMethodsService;

class SynchronizePaymentMethods
{
    public function __construct(
        private readonly SynchronizePaymentMethodsService $synchronizePaymentMethods
    ) {
    }

    public function execute(): void
    {
        $this->synchronizePaymentMethods->execute();
    }
}
