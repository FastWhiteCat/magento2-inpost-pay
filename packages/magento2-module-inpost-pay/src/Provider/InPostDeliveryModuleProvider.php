<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use Magento\Framework\Module\Manager as ModuleManager;

class InPostDeliveryModuleProvider
{
    private const INPOST_DELIVERY_MODULE_NAME = 'Smartmage_Inpost';

    public function __construct(
        private readonly ModuleManager $moduleManager
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleManager->isEnabled(self::INPOST_DELIVERY_MODULE_NAME);
    }
}
