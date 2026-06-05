<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;

class OrderCreatePlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/order';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(OrderCreateInterface $subject, OrderInterface $result): OrderInterface
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
