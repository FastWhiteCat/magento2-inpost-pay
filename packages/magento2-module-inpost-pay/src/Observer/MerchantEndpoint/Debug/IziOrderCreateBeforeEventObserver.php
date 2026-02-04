<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Debug;

use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IziOrderCreateBeforeEventObserver extends MerchantEndpointEventObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Order Create request';

    public function execute(Observer $observer): void
    {
        if ($this->canDebug()) {
            $event = $observer->getEvent();

            $orderDetailsData = [];
            $orderDetails = $event->getData(OrderInterface::ORDER_DETAILS);
            if ($orderDetails instanceof ExtensibleDataInterface) {
                $orderDetailsData = $this->objectConverter->toNestedArray(
                    $orderDetails,
                    [],
                    OrderDetailsInterface::class
                );
            }

            $accountInfoData = [];
            $accountInfo = $event->getData(OrderInterface::ACCOUNT_INFO);
            if ($accountInfo instanceof ExtensibleDataInterface) {
                $accountInfoData = $this->objectConverter->toNestedArray(
                    $accountInfo,
                    [],
                    AccountInfoInterface::class
                );
            }

            $deliveryData = [];
            $delivery = $event->getData(OrderInterface::DELIVERY);
            if ($delivery instanceof ExtensibleDataInterface) {
                $deliveryData = $this->objectConverter->toNestedArray($delivery, [], DeliveryInterface::class);
            }

            $invoiceDetailsData = [];
            $invoiceDetails = $event->getData(OrderInterface::INVOICE_DETAILS);
            if ($invoiceDetails instanceof ExtensibleDataInterface) {
                $invoiceDetailsData = $this->objectConverter->toNestedArray(
                    $invoiceDetails,
                    [],
                    InvoiceDetailsInterface::class
                );
            }

            $consentsData = $this->getConsentsData($event);

            $requestParams = [
                OrderInterface::ORDER_DETAILS => $orderDetailsData,
                OrderInterface::ACCOUNT_INFO => $accountInfoData,
                OrderInterface::DELIVERY => $deliveryData,
                OrderInterface::CONSENTS => $consentsData,
                OrderInterface::INVOICE_DETAILS => $invoiceDetailsData
            ];

            if ($this->debugConfigProvider->isAnonymisingEnabled()) {
                $this->createEventDataLog($this->anonymizeArray($requestParams));
            } else {
                $this->createEventDataLog($requestParams);
            }
        }
    }

    private function getConsentsData(Event $event): array
    {
        $consentsData = [];
        $consents = $event->getData(OrderInterface::CONSENTS);
        if (is_array($consents)) {
            foreach ($consents as $consent) {
                if ($consent instanceof ExtensibleDataInterface) {
                    $consentsData[] = $this->objectConverter->toNestedArray(
                        $consent,
                        [],
                        AcceptedConsentInterface::class
                    );
                }
            }
        }

        return $consentsData;
    }
}
