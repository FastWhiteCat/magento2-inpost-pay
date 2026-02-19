<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetails\AdditionalOrderParametersInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class OrderDetails extends DataObject implements OrderDetailsInterface, ExtensibleDataInterface
{
    private const DEFAULT_CURRENCY = 'PLN';

    /**
     * @param PriceInterfaceFactory $priceFactory
     * @param array $data
     */
    public function __construct(
        private readonly PriceInterfaceFactory $priceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getBasketId(): string
    {
        $basketId = $this->getData(self::BASKET_ID);

        return (is_scalar($basketId)) ? (string)$basketId : '';
    }

    /**
     * @param string $basketId
     * @return void
     */
    public function setBasketId(string $basketId): void
    {
        $this->setData(self::BASKET_ID, $basketId);
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        $orderId = $this->getData(self::ORDER_ID);

        return (is_scalar($orderId)) ? (string)$orderId : '';
    }

    /**
     * @param string $orderId
     * @return void
     */
    public function setOrderId(string $orderId): void
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    public function getCustomerOrderId(): string
    {
        $customerOrderId = $this->getData(self::CUSTOMER_ORDER_ID);

        return (is_scalar($customerOrderId)) ? (string)$customerOrderId : '';
    }

    /**
     * @param string $customerOrderId
     * @return void
     */
    public function setCustomerOrderId(string $customerOrderId): void
    {
        $this->setData(self::CUSTOMER_ORDER_ID, $customerOrderId);
    }

    /**
     * @return float
     */
    public function getOrderDiscount(): float
    {
        $orderDiscount = $this->getData(self::ORDER_DISCOUNT);

        return (is_scalar($orderDiscount)) ? (float)$orderDiscount : 0.00;
    }

    /**
     * @param float $orderDiscount
     * @return void
     */
    public function setOrderDiscount(float $orderDiscount): void
    {
        $this->setData(self::ORDER_DISCOUNT, $orderDiscount);
    }

    /**
     * @return string
     */
    public function getOrderComments(): string
    {
        $orderComments = $this->getData(self::ORDER_COMMENTS);

        return (is_scalar($orderComments)) ? (string)$orderComments : '';
    }

    /**
     * @param string $orderComments
     * @return void
     */
    public function setOrderComments(string $orderComments): void
    {
        $this->setData(self::ORDER_COMMENTS, $orderComments);
    }

    /**
     * @return PriceInterface|null
     */
    public function getBasketPrice(): ?PriceInterface
    {
        $basketPrice = $this->getData(self::BASKET_PRICE);

        if ($basketPrice instanceof PriceInterface) {
            return $basketPrice;
        }

        return null;
    }

    /**
     * @param PriceInterface $basketPrice
     * @return void
     */
    public function setBasketPrice(PriceInterface $basketPrice): void
    {
        $this->setData(self::BASKET_PRICE, $basketPrice);
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        $currency = $this->getData(self::CURRENCY);

        return (is_scalar($currency)) ? (string)$currency : self::DEFAULT_CURRENCY;
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        $paymentType = $this->getData(self::PAYMENT_TYPE);

        return (is_scalar($paymentType)) ? (string)$paymentType : '';
    }

    /**
     * @param string $paymentType
     * @return void
     */
    public function setPaymentType(string $paymentType): void
    {
        $this->setData(self::PAYMENT_TYPE, $paymentType);
    }

    /**
     * @return string
     */
    public function getPosId(): string
    {
        $posId = $this->getData(self::POS_ID);

        return (is_scalar($posId)) ? (string)$posId : '';
    }

    /**
     * @param string $posId
     * @return void
     */
    public function setPosId(string $posId): void
    {
        $this->setData(self::POS_ID, $posId);
    }

    /**
     * @return string
     */
    public function getOrderCreationDate(): string
    {
        $orderCreationDate = $this->getData(self::ORDER_CREATION_DATE);

        return (is_scalar($orderCreationDate)) ? (string)$orderCreationDate : '';
    }

    /**
     * @param string $orderCreationDate
     * @return void
     */
    public function setOrderCreationDate(string $orderCreationDate): void
    {
        $this->setData(self::ORDER_CREATION_DATE, $orderCreationDate);
    }

    /**
     * @return string
     */
    public function getOrderMerchantStatusDescription(): string
    {
        $orderMerchantStatusDescription = $this->getData(self::ORDER_MERCHANT_STATUS_DESCRIPTION);

        return (is_scalar($orderMerchantStatusDescription)) ? (string)$orderMerchantStatusDescription : '';
    }

    /**
     * @param string $orderMerchantStatusDescription
     * @return void
     */
    public function setOrderMerchantStatusDescription(string $orderMerchantStatusDescription): void
    {
        $this->setData(self::ORDER_MERCHANT_STATUS_DESCRIPTION, $orderMerchantStatusDescription);
    }

    /**
     * @return PriceInterface
     */
    public function getOrderBasePrice(): PriceInterface
    {
        $orderBasePrice = $this->getData(self::ORDER_BASE_PRICE);

        if ($orderBasePrice instanceof PriceInterface) {
            return $orderBasePrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param PriceInterface $orderBasePrice
     * @return void
     */
    public function setOrderBasePrice(PriceInterface $orderBasePrice): void
    {
        $this->setData(self::ORDER_BASE_PRICE, $orderBasePrice);
    }

    /**
     * @return PriceInterface
     */
    public function getOrderFinalPrice(): PriceInterface
    {
        $orderFinalPrice = $this->getData(self::ORDER_FINAL_PRICE);

        if ($orderFinalPrice instanceof PriceInterface) {
            return $orderFinalPrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param PriceInterface $orderFinalPrice
     * @return void
     */
    public function setOrderFinalPrice(PriceInterface $orderFinalPrice): void
    {
        $this->setData(self::ORDER_FINAL_PRICE, $orderFinalPrice);
    }

    /**
     * @return string[]
     */
    public function getDeliveryReferencesList(): array
    {
        $deliveryReferencesList = $this->getData(self::DELIVERY_REFERENCE_LIST);

        return (is_array($deliveryReferencesList)) ? $deliveryReferencesList : [];
    }

    /**
     * @param string[] $deliveryReferencesList
     * @return void
     */
    public function setDeliveryReferencesList(array $deliveryReferencesList): void
    {
        $this->setData(self::DELIVERY_REFERENCE_LIST, $deliveryReferencesList);
    }

    /**
     * @return AdditionalOrderParametersInterface[]
     */
    public function getOrderAdditionalParameters(): array
    {
        $params = $this->getData(self::ORDER_ADDITIONAL_PARAMETERS);

        return (is_array($params)) ? $params : [];
    }

    /**
     * @param AdditionalOrderParametersInterface[] $params
     * @return void
     */
    public function setOrderAdditionalParameters(array $params): void
    {
        $this->setData(self::ORDER_ADDITIONAL_PARAMETERS, $params);
    }
}
