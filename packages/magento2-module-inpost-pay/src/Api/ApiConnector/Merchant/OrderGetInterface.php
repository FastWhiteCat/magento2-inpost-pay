<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;

/**
 * InPost Pay Order service that allows for getting order data.
 * @api
 */
interface OrderGetInterface
{
    public const ORDER_ID = 'order_id';

    /**
     * @param string $orderId
     * @return \InPost\InPostPay\Api\Data\Merchant\OrderInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\OrderNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(string $orderId): OrderInterface;
}
