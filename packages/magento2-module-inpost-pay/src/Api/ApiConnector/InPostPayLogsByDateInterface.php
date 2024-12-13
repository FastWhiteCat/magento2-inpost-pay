<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector;

use InPost\InPostPay\Api\Data\Merchant\Logs\InPostPayLogInterface;

interface InPostPayLogsByDateInterface
{
    /**
     * @param string $logDate
     * @return \InPost\InPostPay\Api\Data\Merchant\Logs\InPostPayLogInterface
     */
    public function execute(string $logDate): InPostPayLogInterface;
}
