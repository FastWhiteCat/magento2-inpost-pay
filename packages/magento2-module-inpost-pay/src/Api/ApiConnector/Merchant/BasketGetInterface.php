<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\BasketInterface as BasketDataInterface;

/**
 * InPost Pay Basket service that allows for getting basket data.
 * @api
 */
interface BasketGetInterface
{
    /**
     * @param string $basketId
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(string $basketId): BasketDataInterface;
}
