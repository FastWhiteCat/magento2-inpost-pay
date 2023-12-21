<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderGetInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziOrderGetBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Order Get request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $orderId = $event->getData(OrderGetInterface::ORDER_ID);
            $requestParams = [OrderGetInterface::ORDER_ID => $orderId];

            $this->createEventDataLog($requestParams);
        }
    }
}
