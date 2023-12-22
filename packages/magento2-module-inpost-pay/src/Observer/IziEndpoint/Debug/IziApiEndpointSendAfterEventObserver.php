<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\IziEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziApiEndpointSendAfterEventObserver extends IziApiEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'SENDING: IZI API response';

    public function execute(Observer $observer)
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $response = $event->getData(ConnectorInterface::RESPONSE);
            $responseData = (is_array($response)) ? $response : [];
            $this->createEventDataLog($responseData);
        }
    }
}
