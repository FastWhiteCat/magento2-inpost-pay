<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

/**
 * InPost Pay Basket service that allows for deleting basket.
 * @api
 */
interface BasketDeleteInterface
{
    /**
     * @param string $basketId
     * @return void
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(string $basketId): void;
}
