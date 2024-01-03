<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziBasketUpdateAfterEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Basket Update response';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $basketData = [];
            $basket = $event->getData(BasketConfirmationInterface::BASKET);
            if ($basket instanceof ExtensibleDataInterface) {
                $basketData = $this->objectConverter->toNestedArray($basket, [], BasketInterface::class);
            }

            $this->createEventDataLog($basketData);
        }
    }
}
