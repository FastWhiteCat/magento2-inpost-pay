<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InPostPayDigitalDeliveryException extends OrderNotCreateException
{
    protected string $errorMsg = 'Unable to place order with digital delivery.';
}
