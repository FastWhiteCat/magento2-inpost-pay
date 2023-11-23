<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\BasketInformationResponseInterface;
use Magento\Framework\Model\AbstractModel;

class BasketInformationResponse extends AbstractModel implements BasketInformationResponseInterface
{
    public function setBasketId(string $basketId): BasketInformationResponseInterface
    {
        return $this->setData(self::BASKET_ID, $basketId);
    }

    public function getBasketId(): string
    {
        return (string)$this->getData(self::BASKET_ID);
    }

    public function setQrCode(?string $qrCode): BasketInformationResponseInterface
    {
        return $this->setData(self::QR_CODE, $qrCode);
    }

    public function getQrCode(): ?string
    {
        return $this->getData(self::QR_CODE) ? (string)$this->getData(self::QR_CODE) : null;
    }

    public function setDeepLink(?string $deepLink): BasketInformationResponseInterface
    {
        return $this->setData(self::DEEP_LINK, $deepLink);
    }

    public function getDeepLink(): ?string
    {
        return $this->getData(self::DEEP_LINK) ? (string)$this->getData(self::DEEP_LINK) : null;
    }

    public function setDeepLinkHms(?string $deepLinkHms): BasketInformationResponseInterface
    {
        return $this->setData(self::DEEP_LINK_HMS, $deepLinkHms);
    }

    public function getDeepLinkHms(): ?string
    {
        return $this->getData(self::DEEP_LINK_HMS) ? (string)$this->getData(self::DEEP_LINK_HMS) : null;
    }
}
