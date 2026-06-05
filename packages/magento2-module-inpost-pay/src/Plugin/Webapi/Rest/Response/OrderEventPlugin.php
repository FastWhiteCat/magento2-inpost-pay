<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderUpdateInterface;

class OrderEventPlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/order/:orderId/event';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(OrderEventInterface $subject, OrderUpdateInterface $result): OrderUpdateInterface
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
