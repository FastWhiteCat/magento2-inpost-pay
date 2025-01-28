<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class QuoteChangedDuringOrderProcessingException extends OrderNotCreateException
{
    protected string $errorMsg = 'Basket has been changed during order processing. Please try again.';
}
