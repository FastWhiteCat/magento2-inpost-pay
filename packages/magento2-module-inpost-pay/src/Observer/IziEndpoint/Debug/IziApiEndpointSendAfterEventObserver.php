<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\IziEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Traits\AnonymizerTrait;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziApiEndpointSendAfterEventObserver extends IziApiEndpointEventObserver implements ObserverInterface
{
    use AnonymizerTrait;

    protected string $eventDescription = 'SENDING: IZI API response';

    public function execute(Observer $observer)
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $response = $event->getData(ConnectorInterface::RESPONSE);
            $responseData = (is_array($response)) ? $response : [];

            if ($this->debugConfigProvider->isAnonymisingEnabled()) {
                $this->createEventDataLog($this->anonymizeArray($responseData));
            } else {
                $this->createEventDataLog($responseData);
            }
        }
    }
}
