<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;

interface OrderDetailsInterface
{
    public const BASKET_ID = 'basket_id';
    public const CURRENCY = 'currency';
    public const BASKET_PRICE = 'basket_price';
    public const PAYMENT_TYPE = 'payment_type';
    public const ORDER_COMMENTS = 'order_comments';
    public const COMMENTS = 'comments';
    public const ORDER_ID = 'order_id';
    public const CUSTOMER_ORDER_ID = 'customer_order_id';
    public const ORDER_DISCOUNT = 'order_discount';
    public const POS_ID = 'pos_id';
    public const ORDER_CREATION_DATE = 'order_creation_date';
    public const ORDER_MERCHANT_STATUS_DESCRIPTION = 'order_merchant_status_description';
    public const ORDER_BASE_PRICE = 'order_base_price';
    public const ORDER_FINAL_PRICE = 'order_final_price';
    public const DELIVERY_REFERENCE_LIST = 'delivery_references_list';
    public const ORDER_ADDITIONAL_PARAMETERS = 'order_additional_parameters';
    public const FREE_ORDER = 'FREE_ORDER';

    /**
     * @return string
     */
    public function getBasketId(): string;

    /**
     * @param string $basketId
     * @return void
     */
    public function setBasketId(string $basketId): void;

    /**
     * @return string
     */
    public function getOrderId(): string;

    /**
     * @param string $orderId
     * @return void
     */
    public function setOrderId(string $orderId): void;

    /**
     * @return string
     */
    public function getCustomerOrderId(): string;

    /**
     * @param string $customerOrderId
     * @return void
     */
    public function setCustomerOrderId(string $customerOrderId): void;

    /**
     * @return float
     */
    public function getOrderDiscount(): float;

    /**
     * @param float $orderDiscount
     * @return void
     */
    public function setOrderDiscount(float $orderDiscount): void;

    /**
     * @return string
     */
    public function getOrderComments(): string;

    /**
     * @param string $orderComments
     * @return void
     */
    public function setOrderComments(string $orderComments): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null
     */
    public function getBasketPrice(): ?PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketPrice
     * @return void
     */
    public function setBasketPrice(PriceInterface $basketPrice): void;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void;

    /**
     * @return string
     */
    public function getPaymentType(): string;

    /**
     * @param string $paymentType
     * @return void
     */
    public function setPaymentType(string $paymentType): void;

    /**
     * @return string
     */
    public function getPosId(): string;

    /**
     * @param string $posId
     * @return void
     */
    public function setPosId(string $posId): void;

    /**
     * @return string
     */
    public function getOrderCreationDate(): string;

    /**
     * @param string $orderCreationDate
     * @return void
     */
    public function setOrderCreationDate(string $orderCreationDate): void;

    /**
     * @return string
     */
    public function getOrderMerchantStatusDescription(): string;

    /**
     * @param string $orderMerchantStatusDescription
     * @return void
     */
    public function setOrderMerchantStatusDescription(string $orderMerchantStatusDescription): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getOrderBasePrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $orderBasePrice
     * @return void
     */
    public function setOrderBasePrice(PriceInterface $orderBasePrice): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getOrderFinalPrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $orderFinalPrice
     * @return void
     */
    public function setOrderFinalPrice(PriceInterface $orderFinalPrice): void;

    /**
     * @return string[]
     */
    public function getDeliveryReferencesList(): array;

    /**
     * @param string[] $deliveryReferencesList
     * @return void
     */
    public function setDeliveryReferencesList(array $deliveryReferencesList): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\OrderDetails\AdditionalOrderParametersInterface[]
     */
    public function getOrderAdditionalParameters(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\OrderDetails\AdditionalOrderParametersInterface[] $params
     * @return void
     */
    public function setOrderAdditionalParameters(array $params): void;
}
