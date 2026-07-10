<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketUpdateInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

class BasketUpdatePlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/basket/:basketId/event';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(BasketUpdateInterface $subject, BasketInterface $result): BasketInterface
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
