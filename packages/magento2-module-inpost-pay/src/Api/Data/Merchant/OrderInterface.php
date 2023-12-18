<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;

interface OrderInterface
{
    public const ORDER_DETAILS = 'order_details';
    public const INVOICE_DETAILS = 'invoice_details';
    public const ACCOUNT_INFO = 'account_info';
    public const DELIVERY = 'delivery';
    public const CONSENTS = 'consents';
    public const PRODUCTS = 'products';

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface
     */
    public function getOrderDetails(): OrderDetailsInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface $orderDetails
     * @return void
     */
    public function setOrderDetails(OrderDetailsInterface $orderDetails): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface|null
     */
    public function getInvoiceDetails(): ?InvoiceDetailsInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface|null $invoiceDetails
     * @return void
     */
    public function setInvoiceDetails(?InvoiceDetailsInterface $invoiceDetails): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface
     */
    public function getAccountInfo(): AccountInfoInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface $accountInfo
     * @return void
     */
    public function setAccountInfo(AccountInfoInterface $accountInfo): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface
     */
    public function getDelivery(): DeliveryInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface $delivery
     * @return void
     */
    public function setDelivery(DeliveryInterface $delivery): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface[]
     */
    public function getConsents(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface[] $consents
     * @return void
     */
    public function setConsents(array $consents): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[]
     */
    public function getProducts(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void;
}
