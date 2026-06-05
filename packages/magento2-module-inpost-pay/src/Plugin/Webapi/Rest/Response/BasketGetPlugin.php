<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketGetInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

class BasketGetPlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/basket/:basketId';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(BasketGetInterface $subject, BasketInterface $result): BasketInterface
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
