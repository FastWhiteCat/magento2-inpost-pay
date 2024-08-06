<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class PhoneNumber extends DataObject implements PhoneNumberInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getCountryPrefix(): string
    {
        $countryPrefix = $this->getData(self::COUNTRY_PREFIX);

        return (is_scalar($countryPrefix)) ? (string)$countryPrefix : '';
    }

    /**
     * @param string $countryPrefix
     * @return void
     */
    public function setCountryPrefix(string $countryPrefix): void
    {
        $this->setData(self::COUNTRY_PREFIX, $countryPrefix);
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        $phone = $this->getData(self::PHONE);

        return (is_scalar($phone)) ? (string)$phone : '';
    }

    /**
     * @param string $phone
     * @return void
     */
    public function setPhone(string $phone): void
    {
        $this->setData(self::PHONE, $phone);
    }
}
