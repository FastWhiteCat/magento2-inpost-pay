<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\RefundEventInterface;

class RefundEventPlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/refund/events';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(RefundEventInterface $subject, bool $result): bool
    {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
