<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

class BasketConfirmationPlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/basket/:basketId/confirmation';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(BasketConfirmationInterface $subject, BasketInterface $result): BasketInterface
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
