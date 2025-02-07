<?php

declare(strict_types=1);

namespace InPost\InPostPay\Registry\Order\Email\Sender;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;

class InPostPayOrderEmailSenderRegistry
{
    private ?InPostPayOrderInterface $inPostPayOrder = null;

    public function register(InPostPayOrderInterface $inPostPayOrder): void
    {
        $this->inPostPayOrder = $inPostPayOrder;
    }

    public function registry(): ?InPostPayOrderInterface
    {
        return $this->inPostPayOrder;
    }
}
