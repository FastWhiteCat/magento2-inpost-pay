<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class AddressDetails extends DataObject implements AddressDetailsInterface, ExtensibleDataInterface
{
    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        $street = $this->getData(self::STREET);

        return (is_scalar($street)) ? (string)$street : '';
    }

    /**
     * @param string|null $street
     * @return void
     */
    public function setStreet(?string $street): void
    {
        $this->setData(self::STREET, $street);
    }

    /**
     * @return string|null
     */
    public function getBuilding(): ?string
    {
        $building = $this->getData(self::BUILDING);

        return (is_scalar($building)) ? (string)$building : '';
    }

    /**
     * @param string|null $building
     * @return void
     */
    public function setBuilding(?string $building): void
    {
        $this->setData(self::BUILDING, $building);
    }

    /**
     * @return string|null
     */
    public function getFlat(): ?string
    {
        $flat = $this->getData(self::FLAT);

        return (is_scalar($flat)) ? (string)$flat : '';
    }

    /**
     * @param string|null $flat
     * @return void
     */
    public function setFlat(?string $flat): void
    {
        $this->setData(self::FLAT, $flat);
    }
}
