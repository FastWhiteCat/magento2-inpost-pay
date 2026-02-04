<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketUpdateInterface;
use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziOrderUpdateBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Order Event request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $orderId = $event->getData(InPostPayOrderInterface::ORDER_ID);
            $eventId = $event->getData(BasketUpdateInterface::EVENT_ID);
            $eventDataTime = $event->getData(BasketUpdateInterface::EVENT_DATA_TIME);

            $updateEventData = [];
            $updateEvent = $event->getData(OrderEventInterface::EVENT_DATA);
            if ($updateEvent instanceof ExtensibleDataInterface) {
                $updateEventData = $this->objectConverter->toNestedArray($updateEvent, [], EventDataInterface::class);
            }

            $phoneNumberData = [];
            $phoneNumber = $event->getData(InPostPayQuoteInterface::PHONE_NUMBER);
            if ($phoneNumber instanceof ExtensibleDataInterface) {
                $phoneNumberData = $this->objectConverter->toNestedArray($phoneNumber, [], PhoneNumberInterface::class);
            }

            $requestParams = [
                InPostPayOrderInterface::ORDER_ID => $orderId,
                BasketUpdateInterface::EVENT_ID => $eventId,
                BasketUpdateInterface::EVENT_DATA_TIME => $eventDataTime,
                OrderEventInterface::EVENT_DATA => $updateEventData,
                InPostPayOrderInterface::PHONE_NUMBER => $phoneNumberData
            ];

            if ($this->debugConfigProvider->isAnonymisingEnabled()) {
                $this->createEventDataLog($this->anonymizeArray($requestParams));
            } else {
                $this->createEventDataLog($requestParams);
            }
        }
    }
}
