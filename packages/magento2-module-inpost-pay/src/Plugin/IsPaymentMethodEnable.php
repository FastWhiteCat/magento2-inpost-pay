<?php
namespace InPost\InPostPay\Plugin;

use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Exception\InPostPayInternalException;

class IsPaymentMethodEnable
{
    public function __construct(private readonly GeneralConfigProvider $generalConfigProvider)
    {
    }

    public function beforeExecute(): void
    {
        if (!$this->generalConfigProvider->isEnabled()) {
            throw new InPostPayInternalException(__('InPostPay payment method is not enabled'));
        }
    }
}
