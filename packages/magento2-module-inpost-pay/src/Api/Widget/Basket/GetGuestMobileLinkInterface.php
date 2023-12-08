<?php

namespace InPost\InPostPay\Api\Widget\Basket;

/**
 * @api
 */
interface GetGuestMobileLinkInterface
{
    /**
     * @param string $cartId
     *
     * @return MobileLinkInterface
     */
    public function execute(string $cartId): MobileLinkInterface;
}
