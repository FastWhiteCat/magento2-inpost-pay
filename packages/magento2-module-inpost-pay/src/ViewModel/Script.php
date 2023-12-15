<?php

declare(strict_types=1);

namespace InPost\InPostPay\ViewModel;

use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Script implements ArgumentInterface
{
    /**
     * @param SandboxConfigProvider $sandboxConfigProvider
     * @param DisplayConfigProvider $displayConfigProvider
     */
    public function __construct(
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly DisplayConfigProvider $displayConfigProvider,
    ) {
    }

    /**
     * @return bool
     */
    public function isSandboxEnabled(): bool
    {
        return $this->sandboxConfigProvider->isSandboxEnabled();
    }

    /**
     * @return bool
     */
    public function isEnabledInMiniCart(): bool
    {
        return $this->displayConfigProvider->isEnabledInMiniCart();
    }
}
