<?php

namespace InPost\InPostPay\Api\Widget\Basket;

/**
 * @api
 */
interface GetGuestConfirmationInterface
{
    /**
     * @param string $cartId
     *
     * @return \InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface
     */
    public function execute(string $cartId): \InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface;
}
