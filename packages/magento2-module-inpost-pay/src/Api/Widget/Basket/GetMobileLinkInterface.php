<?php

namespace InPost\InPostPay\Api\Widget\Basket;

/**
 * @api
 */
interface GetMobileLinkInterface
{
    /**
     * @param int $cartId
     *
     * @return MobileLinkInterface
     */
    public function execute(int $cartId): MobileLinkInterface;
}
