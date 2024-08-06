<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziBasketGetBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Basket Get request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $basketId = $event->getData(InPostPayQuoteInterface::BASKET_ID);
            $requestParams = [InPostPayQuoteInterface::BASKET_ID => $basketId];

            $this->createEventDataLog($requestParams);
        }
    }
}
