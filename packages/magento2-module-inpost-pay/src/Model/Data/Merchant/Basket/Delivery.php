<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Delivery extends DataObject implements DeliveryInterface, ExtensibleDataInterface
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

    /**
     * @return string
     */
    public function getDeliveryType(): string
    {
        $deliveryType = $this->getData(self::DELIVERY_TYPE);

        return is_scalar($deliveryType) ? (string)$deliveryType : '';
    }

    /**
     * @param string $deliveryType
     * @return void
     */
    public function setDeliveryType(string $deliveryType): void
    {
        $this->setData(self::DELIVERY_TYPE, $deliveryType);
    }

    /**
     * @return string
     */
    public function getDeliveryDate(): string
    {
        $deliveryDate = $this->getData(self::DELIVERY_DATE);

        return is_scalar($deliveryDate) ? (string)$deliveryDate : '';
    }

    /**
     * @param string $deliveryDate
     * @return void
     */
    public function setDeliveryDate(string $deliveryDate): void
    {
        $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }

    /**
     * @return array|\InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface[]
     */
    public function getDeliveryOptions(): array
    {
        $deliveryOptions = $this->getData(self::DELIVERY_OPTIONS);

        return is_array($deliveryOptions) ? $deliveryOptions : [];
    }

    /**
     * @param array $deliveryOptions
     * @return void
     */
    public function setDeliveryOptions(array $deliveryOptions): void
    {
        $this->setData(self::DELIVERY_OPTIONS, $deliveryOptions);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getDeliveryPrice(): PriceInterface
    {
        $deliveryPrice = $this->getData(self::DELIVERY_PRICE);

        if ($deliveryPrice instanceof PriceInterface) {
            return $deliveryPrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $deliveryPrice
     * @return void
     */
    public function setDeliveryPrice(PriceInterface $deliveryPrice): void
    {
        $this->setData(self::DELIVERY_PRICE, $deliveryPrice);
    }

    /**
     * @return float|null
     */
    public function getFreeDeliveryMinimumGrossPrice(): ?float
    {
        $freeDeliveryMinimumGrossPrice = $this->getData(self::FREE_DELIVERY_MINIMUM_GROSS_PRICE);

        return is_float($freeDeliveryMinimumGrossPrice) ? (float)$freeDeliveryMinimumGrossPrice : null;
    }

    /**
     * @param float $freeDeliveryMinimumGrossPrice
     * @return void
     */
    public function setFreeDeliveryMinimumGrossPrice(float $freeDeliveryMinimumGrossPrice): void
    {
        $this->setData(self::FREE_DELIVERY_MINIMUM_GROSS_PRICE, $freeDeliveryMinimumGrossPrice);
    }
}
