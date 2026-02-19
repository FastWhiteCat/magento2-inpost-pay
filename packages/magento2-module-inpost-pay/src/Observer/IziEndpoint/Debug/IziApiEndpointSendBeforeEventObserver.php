<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\IziEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziApiEndpointSendBeforeEventObserver extends IziApiEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'SENDING: IZI API request';

    public function execute(Observer $observer)
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $requestData = [];
            $request = $event->getData(ConnectorInterface::REQUEST);
            if ($request instanceof RequestInterface) {
                $requestData = [
                    'method' => $request->getMethod(),
                    'endpoint_url' => sprintf(
                        '%s/%s',
                        trim($request->getApiUrl(), '/'),
                        trim($request->getUri(true), '/')
                    ),
                    'headers' => $request->getHeaders(true),
                    'params' => $request->getParams()
                ];
            }

            if ($this->debugConfigProvider->isAnonymisingEnabled()) {
                $this->createEventDataLog($this->anonymizeArray($requestData));
            } else {
                $this->createEventDataLog($requestData);
            }
        }
    }
}
