<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziBestsellerProductsGetBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Bestseller Products Get request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $data = $observer->getEvent()->getData();

            if (is_array($data)) {
                unset($data['name']);
                $this->createEventDataLog($data);
            }
        }
    }
}
