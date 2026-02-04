<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderUpdateInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziOrderUpdateAfterEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Order Event response';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $orderUpdateData = [];
            $orderUpdate = $event->getData(OrderEventInterface::ORDER_UPDATE);
            if ($orderUpdate instanceof ExtensibleDataInterface) {
                $orderUpdateData = $this->objectConverter->toNestedArray(
                    $orderUpdate,
                    [],
                    OrderUpdateInterface::class
                );
            }

            if ($this->debugConfigProvider->isAnonymisingEnabled()) {
                $this->createEventDataLog($this->anonymizeArray($orderUpdateData));
            } else {
                $this->createEventDataLog($orderUpdateData);
            }
        }
    }
}
