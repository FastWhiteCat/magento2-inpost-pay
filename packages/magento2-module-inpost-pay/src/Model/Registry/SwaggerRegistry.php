<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Registry;

class SwaggerRegistry
{
    private bool $isAllowed = false;

    /**
     * @param bool $isAllowed
     * @return void
     */
    public function setIsAllowed(bool $isAllowed): void
    {
        $this->isAllowed = $isAllowed;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }
}
