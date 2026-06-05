<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketDeleteInterface;

class BasketDeletePlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/basket/:basketId/binding';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(BasketDeleteInterface $subject): void
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
    }
}
