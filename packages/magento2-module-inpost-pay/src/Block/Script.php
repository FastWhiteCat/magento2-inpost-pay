<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block;

use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Script extends Template
{
    /**
     * @param SandboxConfigProvider $sandboxConfigProvider
     * @param DisplayConfigProvider $displayConfigProvider
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly DisplayConfigProvider $displayConfigProvider,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
