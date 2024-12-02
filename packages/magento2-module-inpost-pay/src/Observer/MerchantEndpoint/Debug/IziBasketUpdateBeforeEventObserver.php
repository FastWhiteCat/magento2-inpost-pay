<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketUpdateInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziBasketUpdateBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Basket Update request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();
            $basketId = $event->getData(InPostPayQuoteInterface::BASKET_ID);
            $eventId = $event->getData(BasketUpdateInterface::EVENT_ID);
            $eventDataTime = $event->getData(BasketUpdateInterface::EVENT_DATA_TIME);
            $eventType = $event->getData(BasketUpdateInterface::EVENT_TYPE);

            $quantityEventDataRecords = $this->getQuantityEventData($event);
            $relatedProductsEventDataRecords = $this->getRelatedProductsEventDataArray($event);
            $promoCodesEventDataRecords = $this->getPromoCodesEventDataArray($event);

            $requestParams = [
                InPostPayQuoteInterface::BASKET_ID => $basketId,
                BasketUpdateInterface::EVENT_ID => $eventId,
                BasketUpdateInterface::EVENT_DATA_TIME => $eventDataTime,
                BasketUpdateInterface::EVENT_TYPE => $eventType,
                BasketUpdateInterface::QUANTITY_EVENT_DATA => $quantityEventDataRecords,
                BasketUpdateInterface::RELATED_PRODUCTS_EVENT_DATA => $relatedProductsEventDataRecords,
                BasketUpdateInterface::PROMO_CODES_EVENT_DATA => $promoCodesEventDataRecords
            ];

            $this->createEventDataLog($requestParams);
        }
    }

    private function getQuantityEventData(Event $event): array
    {
        $quantityEventDataRecords = [];
        $quantityEventData = $event->getData(BasketUpdateInterface::QUANTITY_EVENT_DATA);
        if (is_array($quantityEventData)) {
            foreach ($quantityEventData as $quantityEventDataRecord) {
                if ($quantityEventDataRecord instanceof ExtensibleDataInterface) {
                    $quantityEventDataRecords[] = $this->objectConverter->toNestedArray(
                        $quantityEventDataRecord,
                        [],
                        QuantityUpdateInterface::class
                    );
                }
            }
        }

        return $quantityEventDataRecords;
    }

    private function getRelatedProductsEventDataArray(Event $event): array
    {
        $relatedProductsEventDataRecords = [];
        $relatedProductsEventData = $event->getData(BasketUpdateInterface::RELATED_PRODUCTS_EVENT_DATA);
        if (is_array($relatedProductsEventData)) {
            foreach ($relatedProductsEventData as $relatedProductsEventDataRecord) {
                if ($relatedProductsEventDataRecord instanceof ExtensibleDataInterface) {
                    $relatedProductsEventDataRecords[] = $this->objectConverter->toNestedArray(
                        $relatedProductsEventDataRecord,
                        [],
                        QuantityUpdateInterface::class
                    );
                }
            }
        }

        return $relatedProductsEventDataRecords;
    }

    private function getPromoCodesEventDataArray(Event $event): array
    {
        $promoCodesEventDataRecords = [];
        $promoCodesEventData = $event->getData(BasketUpdateInterface::PROMO_CODES_EVENT_DATA);
        if (is_array($promoCodesEventData)) {
            foreach ($promoCodesEventData as $promoCodesEventDataRecord) {
                if ($promoCodesEventDataRecord instanceof ExtensibleDataInterface) {
                    $promoCodesEventDataRecords[] = $this->objectConverter->toNestedArray(
                        $promoCodesEventDataRecord,
                        [],
                        PromoCodeInterface::class
                    );
                }
            }
        }

        return $promoCodesEventDataRecords;
    }
}
