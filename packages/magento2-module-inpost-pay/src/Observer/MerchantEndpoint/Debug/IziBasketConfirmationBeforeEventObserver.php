<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziBasketConfirmationBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Basket Confirmation request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $basketId = $event->getData(InPostPayQuoteInterface::BASKET_ID);
            $status = $event->getData(InPostPayQuoteInterface::STATUS);
            $inPostBasketId = $event->getData(InPostPayQuoteInterface::INPOST_BASKET_ID);
            $maskedPhoneNumber = $event->getData(InPostPayQuoteInterface::MASKED_PHONE_NUMBER);
            $name = $event->getData(sprintf('param_%s', InPostPayQuoteInterface::NAME));
            $name = is_scalar($name) ? (string)$name : '';
            $surname = $event->getData(InPostPayQuoteInterface::SURNAME);
            $surname = is_scalar($surname) ? (string)$surname : '';

            $phoneNumberData = [];
            $phoneNumber = $event->getData(InPostPayQuoteInterface::PHONE_NUMBER);
            if ($phoneNumber instanceof ExtensibleDataInterface) {
                $phoneNumberData = $this->objectConverter->toNestedArray($phoneNumber, [], PhoneNumberInterface::class);
            }

            $browserData = [];
            $browser = $event->getData(BasketConfirmationInterface::BROWSER);
            if ($browser instanceof ExtensibleDataInterface) {
                $browserData = $this->objectConverter->toNestedArray($browser, [], BrowserInterface::class);
            }

            $requestParams = [
                InPostPayQuoteInterface::BASKET_ID => $basketId,
                InPostPayQuoteInterface::STATUS => $status,
                InPostPayQuoteInterface::INPOST_BASKET_ID => $inPostBasketId,
                InPostPayQuoteInterface::PHONE_NUMBER => $phoneNumberData,
                BasketConfirmationInterface::BROWSER => $browserData,
                InPostPayQuoteInterface::MASKED_PHONE_NUMBER => $maskedPhoneNumber,
                InPostPayQuoteInterface::NAME => $name,
                InPostPayQuoteInterface::SURNAME => $surname,
            ];

            if ($this->debugConfigProvider->isAnonymisingEnabled()) {
                $this->createEventDataLog($this->anonymizeArray($requestParams));
            } else {
                $this->createEventDataLog($requestParams);
            }
        }
    }
}
