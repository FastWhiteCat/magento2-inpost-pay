<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class DeliveryProduct extends DataObject implements DeliveryProductInterface, ExtensibleDataInterface
{

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
     * @return bool
     */
    public function isIfDeliveryAvailable(): bool
    {
        $ifDeliveryAvailable = $this->getData(self::IF_DELIVERY_AVAILABLE);

        return (is_bool($ifDeliveryAvailable)) ? $ifDeliveryAvailable : false;
    }

    /**
     * @param bool $ifDeliveryAvailable
     * @return void
     */
    public function setIfDeliveryAvailable(bool $ifDeliveryAvailable): void
    {
        $this->setData(self::IF_DELIVERY_AVAILABLE, $ifDeliveryAvailable);
    }
}
