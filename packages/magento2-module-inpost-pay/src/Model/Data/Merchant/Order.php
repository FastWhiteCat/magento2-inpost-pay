<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Order extends DataObject implements OrderInterface, ExtensibleDataInterface
{
    /**
     * @param AccountInfoInterfaceFactory $accountInfoFactory
     * @param DeliveryInterfaceFactory $deliveryFactory
     * @param OrderDetailsInterfaceFactory $orderDetailsFactory
     * @param array $data
     */
    public function __construct(
        private readonly AccountInfoInterfaceFactory $accountInfoFactory,
        private readonly DeliveryInterfaceFactory $deliveryFactory,
        private readonly OrderDetailsInterfaceFactory $orderDetailsFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return OrderDetailsInterface
     */
    public function getOrderDetails(): OrderDetailsInterface
    {
        $orderDetails = $this->getData(self::ORDER_DETAILS);

        if ($orderDetails instanceof OrderDetailsInterface) {
            return $orderDetails;
        }

        return $this->orderDetailsFactory->create();
    }

    /**
     * @param OrderDetailsInterface $orderDetails
     * @return void
     */
    public function setOrderDetails(OrderDetailsInterface $orderDetails): void
    {
        $this->setData(self::ORDER_DETAILS, $orderDetails);
    }

    /**
     * @return InvoiceDetailsInterface|null
     */
    public function getInvoiceDetails(): ?InvoiceDetailsInterface
    {
        $invoiceDetails = $this->getData(self::INVOICE_DETAILS);

        if ($invoiceDetails instanceof InvoiceDetailsInterface) {
            return $invoiceDetails;
        }

        return null;
    }

    /**
     * @param InvoiceDetailsInterface|null $invoiceDetails
     * @return void
     */
    public function setInvoiceDetails(?InvoiceDetailsInterface $invoiceDetails): void
    {
        $this->setData(self::INVOICE_DETAILS, $invoiceDetails);
    }

    /**
     * @return AccountInfoInterface
     */
    public function getAccountInfo(): AccountInfoInterface
    {
        $accountInfo = $this->getData(self::ACCOUNT_INFO);

        if ($accountInfo instanceof AccountInfoInterface) {
            return $accountInfo;
        }

        return $this->accountInfoFactory->create();
    }

    /**
     * @param AccountInfoInterface $accountInfo
     * @return void
     */
    public function setAccountInfo(AccountInfoInterface $accountInfo): void
    {
        $this->setData(self::ACCOUNT_INFO, $accountInfo);
    }

    /**
     * @return DeliveryInterface
     */
    public function getDelivery(): DeliveryInterface
    {
        $delivery = $this->getData(self::DELIVERY);

        if ($delivery instanceof DeliveryInterface) {
            return $delivery;
        }

        return $this->deliveryFactory->create();
    }

    /**
     * @param DeliveryInterface $delivery
     * @return void
     */
    public function setDelivery(DeliveryInterface $delivery): void
    {
        $this->setData(self::DELIVERY, $delivery);
    }

    /**
     * @return AcceptedConsentInterface[]
     */
    public function getConsents(): array
    {
        $consents = $this->getData(self::CONSENTS);

        return (is_array($consents)) ? $consents : [];
    }

    /**
     * @param AcceptedConsentInterface[] $consents
     * @return void
     */
    public function setConsents(array $consents): void
    {
        $this->setData(self::CONSENTS, $consents);
    }

    /**
     * @return ProductInterface[]
     */
    public function getProducts(): array
    {
        $products = $this->getData(self::PRODUCTS);

        return (is_array($products)) ? $products : [];
    }

    /**
     * @param ProductInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void
    {
        $this->setData(self::PRODUCTS, $products);
    }
}
