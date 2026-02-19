<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\BestsellerProductsGetInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziBestsellerProductsGetAfterEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Bestseller Products Get response';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $bestsellerProductsData = [];
            $response = $event->getData(BestsellerProductsGetInterface::RESPONSE);

            if ($response instanceof ExtensibleDataInterface) {
                $bestsellerProductsData = $this->objectConverter->toNestedArray(
                    $response,
                    [],
                    BestsellersInterface::class
                );
            }

            $this->createEventDataLog($bestsellerProductsData);
        }
    }
}
