<?php

declare(strict_types=1);

namespace InPost\InPostPay\Registry\Order\Creation;

class InPostPayOrderCreationRegistry
{
    private ?string $basketId = null;

    public function register(string $basketId): void
    {
        $this->basketId = $basketId;
    }

    public function registry(): ?string
    {
        return $this->basketId;
    }

    public function clear(): void
    {
        $this->basketId = null;
    }
}
