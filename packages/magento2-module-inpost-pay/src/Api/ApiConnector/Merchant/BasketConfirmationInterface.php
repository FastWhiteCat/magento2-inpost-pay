<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

/**
 * InPost Pay Basket service for confirming bound basket.
 * @api
 */
interface BasketConfirmationInterface
{
    public const QUOTE = 'quote';
    public const BROWSER = 'browser';
    public const BASKET = 'basket';
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
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
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
    ): BasketInterface;
}
