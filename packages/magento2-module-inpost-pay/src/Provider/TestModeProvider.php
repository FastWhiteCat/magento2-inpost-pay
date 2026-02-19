<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use Magento\Framework\App\RequestInterface;

class TestModeProvider
{
    public const URL_PARAMETER_NAME = 'showInPostPay';
    public const VALID_VALUE = 'true';

    /**
     * @param GeneralConfigProvider $generalConfigProvider
     * @param SandboxConfigProvider $sandboxConfigProvider
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * @return bool
     */
    public function isTestModeEnabled(): bool
    {
        return $this->generalConfigProvider->isTestModeEnabled() && !$this->sandboxConfigProvider->isSandboxEnabled();
    }

    /**
     * @return bool
     */
    public function isTestModeRequested(): bool
    {
        $urlParameter = $this->request->getParam(self::URL_PARAMETER_NAME);
        if ($urlParameter === self::VALID_VALUE) {
            return true;
        }
        return false;
    }
}
