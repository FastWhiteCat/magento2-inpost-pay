<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use Magento\Framework\DataObject;

class OrderDetails extends DataObject implements OrderDetailsInterface
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

    public function getBasketId(): string
    {
        $basketId = $this->getData(self::BASKET_ID);

        return (is_scalar($basketId)) ? (string)$basketId : '';
    }

    public function setBasketId(string $basketId): void
    {
        $this->setData(self::BASKET_ID, $basketId);
    }

    public function getOrderComments(): string
    {
        $orderComments = $this->getData(self::ORDER_COMMENTS);

        return (is_scalar($orderComments)) ? (string)$orderComments : '';
    }

    public function setOrderComments(string $orderComments): void
    {
        $this->setData(self::ORDER_COMMENTS, $orderComments);
    }

    public function getBasketPrice(): PriceInterface
    {
        $basketPrice = $this->getData(self::BASKET_PRICE);

        if ($basketPrice instanceof PriceInterface) {
            return $basketPrice;
        }

        return $this->priceFactory->create();
    }

    public function setBasketPrice(PriceInterface $basketPrice): void
    {
        $this->setData(self::BASKET_PRICE, $basketPrice);
    }

    public function getCurrency(): string
    {
        $currency = $this->getData(self::CURRENCY);

        return (is_scalar($currency)) ? (string)$currency : self::DEFAULT_CURRENCY;
    }

    public function setCurrency(string $currency): void
    {
        $this->setData(self::CURRENCY, $currency);
    }

    public function getPaymentType(): string
    {
        $paymentType = $this->getData(self::PAYMENT_TYPE);

        return (is_scalar($paymentType)) ? (string)$paymentType : '';
    }

    public function setPaymentType(string $paymentType): void
    {
        $this->setData(self::PAYMENT_TYPE, $paymentType);
    }
}
