<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class PromoCode extends DataObject implements PromoCodeInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        $name = $this->getData(self::NAME);

        return is_scalar($name) ? (string)$name : '';
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getPromoCodeValue(): string
    {
        $promoCodeValue = $this->getData(self::PROMO_CODE_VALUE);

        return is_scalar($promoCodeValue) ? (string)$promoCodeValue : '';
    }

    /**
     * @param string $promoCodeValue
     * @return void
     */
    public function setPromoCodeValue(string $promoCodeValue): void
    {
        $this->setData(self::PROMO_CODE_VALUE, $promoCodeValue);
    }

    /**
     * @return string|null
     */
    public function getRegulationType(): ?string
    {
        $regulationType = $this->getData(self::REGULATION_TYPE);

        return is_scalar($regulationType) ? (string)$regulationType : null;
    }

    /**
     * @param string|null $regulationType
     * @return void
     */
    public function setRegulationType(?string $regulationType): void
    {
        $this->setData(self::REGULATION_TYPE, $regulationType);
    }
}
