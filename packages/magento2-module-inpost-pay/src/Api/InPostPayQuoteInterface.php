<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Quote\Api\Data\CartInterface;

interface InPostPayQuoteInterface
{
    public function setLockerIdForCart(CartInterface $cart, string $lockerId): void;
}
