<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\BasketInterface as BasketDataInterface;

/**
 * InPost Pay Basket service that allows for getting, updating and deleting basket related to quote.
 * @api
 */
interface BasketInterface
{
    /**
     * @param string $basketId
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     */
    public function get(string $basketId): BasketDataInterface;

    /**
     * @param string $basketId
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     */
    public function update(string $basketId): BasketDataInterface;

    /**
     * @param string $basketId
     *
     * @return void
     */
    public function delete(string $basketId): void;
}
