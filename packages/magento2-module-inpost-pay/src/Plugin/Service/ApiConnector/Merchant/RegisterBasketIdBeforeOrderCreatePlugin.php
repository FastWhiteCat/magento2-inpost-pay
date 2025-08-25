<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Service\ApiConnector\Merchant;

use InPost\InPostPay\Registry\Order\Creation\InPostPayOrderCreationRegistry;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderCreate;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;

class RegisterBasketIdBeforeOrderCreatePlugin
{
    public function __construct(
        private readonly InPostPayOrderCreationRegistry $orderCreationRegistry
    ) {
    }

    /**
     * Register basketId of the cart being created via InPost Pay.
     *
     * @param OrderCreate $subject
     * @param OrderDetailsInterface $orderDetails
     * @param AccountInfoInterface $accountInfo
     * @param DeliveryInterface $delivery
     * @param array $consents
     * @param InvoiceDetailsInterface|null $invoiceDetails
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        OrderCreate $subject,
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): void {
        $basketId = $orderDetails->getBasketId();

        if ($basketId) {
            $this->orderCreationRegistry->register($basketId);
        }
    }
}
