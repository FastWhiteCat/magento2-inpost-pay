<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziBasketBindingDeleteAfterEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Basket Delete response';

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $this->createEventDataLog([]);
        }
    }
}
