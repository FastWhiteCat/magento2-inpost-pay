<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto;

use InPost\InPostPay\Model\Dto\Order\Consent;
use InPost\InPostPay\Model\Dto\Order\DtoAccountInfoFactory;
use InPost\InPostPay\Model\Dto\Order\DtoDeliveryFactory;
use InPost\InPostPay\Model\Dto\Order\DtoOrderDetailsFactory;
use InPost\InPostPay\Model\Dto\Order\DtoInvoiceDetailsFactory;
use InPost\InPostPay\Model\Dto\Order\ConsentFactory;
use InPost\InPostPay\Model\Dto\OrderFactory;

class DtoOrderFactory
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly DtoOrderDetailsFactory $dtoOrderDetailsFactory,
        private readonly DtoInvoiceDetailsFactory $dtoInvoiceDetailsFactory,
        private readonly DtoAccountInfoFactory $dtoAccountInfoFactory,
        private readonly DtoDeliveryFactory $dtoDeliveryFactory,
        private readonly ConsentFactory $dtoConsentFactory
    ) {
    }

    public function create(array $data): Order
    {
        /** @var Order $orderDto */
        $orderDto = $this->orderFactory->create();
        $orderDetailsData = (array)($data[Order::ORDER_DETAILS] ?? []);
        $invoiceDetailsData = (array)($data[Order::INVOICE_DETAILS] ?? []);
        $accountInfoData = (array)($data[Order::ACCOUNT_INFO] ?? []);
        $deliveryData = (array)($data[Order::DELIVERY] ?? []);
        $consentsData = (array)($data[Order::CONSENTS] ?? []);

        // @phpstan-ignore-next-line
        $orderDto->setOrderDetails($this->dtoOrderDetailsFactory->create($orderDetailsData));
        // @phpstan-ignore-next-line
        $orderDto->setAccountInfo($this->dtoAccountInfoFactory->create($accountInfoData));
        // @phpstan-ignore-next-line
        $orderDto->setDelivery($this->dtoDeliveryFactory->create($deliveryData));

        if ($invoiceDetailsData) {
            // @phpstan-ignore-next-line
            $orderDto->setInvoiceDetails($this->dtoInvoiceDetailsFactory->create($invoiceDetailsData));
        }

        $consents = [];
        foreach ($consentsData as $consentData) {
            $consents[] = $this->prepareConsent($consentData);
        }
        $orderDto->setConsents($consents);

        return $orderDto;
    }

    private function prepareConsent(array $consentData): Consent
    {
        /** @var Consent $consent */
        $consent = $this->dtoConsentFactory->create();

        if (isset($consentData[Consent::CONSENT_ID]) && is_scalar($consentData[Consent::CONSENT_ID])) {
            $consent->setConsentId((int)$consentData[Consent::CONSENT_ID]);
        }

        if (isset($consentData[Consent::CONSENT_VERSION]) && is_scalar($consentData[Consent::CONSENT_VERSION])) {
            $consent->setConsentVersion((string)$consentData[Consent::CONSENT_VERSION]);
        }

        if (isset($consentData[Consent::IS_ACCEPTED]) && is_scalar($consentData[Consent::IS_ACCEPTED])) {
            $consent->setIsAccepted((bool)$consentData[Consent::IS_ACCEPTED]);
        }

        return $consent;
    }
}
