<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use Magento\Framework\DataObject;

class Delivery extends DataObject implements DeliveryInterface
{
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

    public function getDeliveryType(): string
    {
        $deliveryType = $this->getData(self::DELIVERY_TYPE);

        return is_scalar($deliveryType) ? (string)$deliveryType : '';
    }

    public function setDeliveryType(string $deliveryType): void
    {
        $this->setData(self::DELIVERY_TYPE, $deliveryType);
    }

    public function getDeliveryDate(): string
    {
        $deliveryDate = $this->getData(self::DELIVERY_DATE);

        return is_scalar($deliveryDate) ? (string)$deliveryDate : '';
    }

    public function setDeliveryDate(string $deliveryDate): void
    {
        $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }

    public function getDeliveryOptions(): array
    {
        $deliveryOptions = $this->getData(self::DELIVERY_OPTIONS);

        return is_array($deliveryOptions) ? $deliveryOptions : [];
    }

    public function setDeliveryOptions(array $deliveryOptions): void
    {
        $this->setData(self::DELIVERY_OPTIONS, $deliveryOptions);
    }

    public function getDeliveryPrice(): PriceInterface
    {
        $deliveryPrice = $this->getData(self::DELIVERY_PRICE);

        if ($deliveryPrice instanceof PriceInterface) {
            return $deliveryPrice;
        }

        return $this->priceFactory->create();
    }

    public function setDeliveryPrice(PriceInterface $deliveryPrice): void
    {
        $this->setData(self::DELIVERY_PRICE, $deliveryPrice);
    }

    public function getFreeDeliveryMinimumGrossPrice(): float
    {
        $freeDeliveryMinimumGrossPrice = $this->getData(self::FREE_DELIVERY_MINIMUM_GROSS_PRICE);

        return is_scalar($freeDeliveryMinimumGrossPrice) ? (float)$freeDeliveryMinimumGrossPrice : 0.00;
    }

    public function setFreeDeliveryMinimumGrossPrice(float $freeDeliveryMinimumGrossPrice): void
    {
        $this->setData(self::FREE_DELIVERY_MINIMUM_GROSS_PRICE, $freeDeliveryMinimumGrossPrice);
    }
}
