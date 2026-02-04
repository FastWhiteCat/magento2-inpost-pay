<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Delivery;

use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class DeliveryOption extends DataObject implements DeliveryOptionInterface, ExtensibleDataInterface
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
    public function getDeliveryName(): string
    {
        $deliveryName = $this->getData(self::DELIVERY_NAME);

        return is_scalar($deliveryName) ? (string)$deliveryName : '';
    }

    /**
     * @param string $deliveryName
     * @return void
     */
    public function setDeliveryName(string $deliveryName): void
    {
        $this->setData(self::DELIVERY_NAME, $deliveryName);
    }

    /**
     * @return string
     */
    public function getDeliveryCodeValue(): string
    {
        $deliveryCodeValue = $this->getData(self::DELIVERY_CODE_VALUE);

        return is_scalar($deliveryCodeValue) ? (string)$deliveryCodeValue : '';
    }

    /**
     * @param string $deliveryCodeValue
     * @return void
     */
    public function setDeliveryCodeValue(string $deliveryCodeValue): void
    {
        $this->setData(self::DELIVERY_CODE_VALUE, $deliveryCodeValue);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getDeliveryOptionPrice(): PriceInterface
    {
        $deliveryOptionPrice = $this->getData(self::DELIVERY_OPTION_PRICE);

        if ($deliveryOptionPrice instanceof PriceInterface) {
            return $deliveryOptionPrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $deliveryOptionPrice
     * @return void
     */
    public function setDeliveryOptionPrice(PriceInterface $deliveryOptionPrice): void
    {
        $this->setData(self::DELIVERY_OPTION_PRICE, $deliveryOptionPrice);
    }
}
