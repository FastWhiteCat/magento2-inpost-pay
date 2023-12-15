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
     */
    public function execute(string $basketId): BasketDataInterface;
}
