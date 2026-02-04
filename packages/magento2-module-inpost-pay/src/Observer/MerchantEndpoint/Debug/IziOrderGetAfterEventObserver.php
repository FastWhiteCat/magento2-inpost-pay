<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziOrderGetAfterEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Order Get response';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $orderData = [];
            $order = $event->getData(OrderCreateInterface::INPOST_ORDER);
            if ($order instanceof ExtensibleDataInterface) {
                $orderData = $this->objectConverter->toNestedArray($order, [], OrderInterface::class);
            }

            if ($this->debugConfigProvider->isAnonymisingEnabled()) {
                $this->createEventDataLog($this->anonymizeArray($orderData));
            } else {
                $this->createEventDataLog($orderData);
            }
        }
    }
}
