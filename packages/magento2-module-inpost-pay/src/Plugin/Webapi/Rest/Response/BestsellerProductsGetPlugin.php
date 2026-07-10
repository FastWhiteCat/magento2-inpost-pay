<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\Merchant\BestsellerProductsGetInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;

class BestsellerProductsGetPlugin extends AbstractVersionHeaderPlugin
{
    protected string $requestPath = '/v1/izi/products';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        BestsellerProductsGetInterface $subject,
        BestsellersInterface $result
    ): BestsellersInterface {
        $this->setVersionHeader();
        $this->logVersionHeader();
        return $result;
    }
}
