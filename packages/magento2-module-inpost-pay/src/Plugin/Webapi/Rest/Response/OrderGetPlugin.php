<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderGetInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;

class OrderGetPlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/order/:orderId';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(OrderGetInterface $subject, OrderInterface $result): OrderInterface
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
