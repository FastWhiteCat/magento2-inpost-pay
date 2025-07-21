<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziInitBasketBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Init Basket request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $productId = $event->getData(ProductInterface::PRODUCT_ID);
            $productId = is_scalar($productId) ? (string)$productId : null;

            $basketId = $event->getData(InPostPayQuoteInterface::BASKET_ID);
            $basketId = is_scalar($basketId) ? (string)$basketId : null;

            $phoneNumberData = [];
            $phoneNumber = $event->getData(InPostPayQuoteInterface::PHONE_NUMBER);

            if ($phoneNumber instanceof ExtensibleDataInterface) {
                $phoneNumberData = $this->objectConverter->toNestedArray(
                    $phoneNumber,
                    [],
                    PhoneNumberInterface::class
                );
            }

            $requestParams = [
                ProductInterface::PRODUCT_ID => $productId,
                InPostPayQuoteInterface::PHONE_NUMBER => $phoneNumberData
            ];

            if ($basketId) {
                $requestParams[InPostPayQuoteInterface::BASKET_ID] = $basketId;
            }

            $this->createEventDataLog($requestParams);
        }
    }
}
