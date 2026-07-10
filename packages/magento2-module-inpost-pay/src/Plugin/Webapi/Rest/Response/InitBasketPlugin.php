<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\InitBasketInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

class InitBasketPlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/basket/product/:productId';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(InitBasketInterface $subject, BasketInterface $result): BasketInterface
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
