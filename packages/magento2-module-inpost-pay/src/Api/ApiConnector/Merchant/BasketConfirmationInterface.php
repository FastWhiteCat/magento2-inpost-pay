<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface as BasketDataInterface;

interface BasketConfirmationInterface
{
    /**
     * @param string $basketId
     * @param string $status
     * @param string $inpostBasketId
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface $phoneNumber
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface $browser
     * @param string $maskedPhoneNumber
     * @param string $name
     * @param string $surname
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     */
    public function execute(
        string $basketId,
        string $status,
        string $inpostBasketId,
        PhoneNumberInterface $phoneNumber,
        BrowserInterface $browser,
        string $maskedPhoneNumber,
        string $name,
        string $surname
    ): BasketDataInterface;
}
