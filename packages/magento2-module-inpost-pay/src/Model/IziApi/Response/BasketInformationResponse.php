<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response;

use Magento\Framework\DataObject;

class BasketInformationResponse extends DataObject
{
    public const BASKET_ID = 'basket_id';
    public const QR_CODE = 'qr_code';
    public const DEEP_LINK = 'deep_link';
    public const DEEP_LINK_HMS = 'deep_link_hms';

    /**
     * @param string $basketId
     * @return BasketInformationResponse
     */
    public function setBasketId(string $basketId): BasketInformationResponse
    {
        return $this->setData(self::BASKET_ID, $basketId);
    }

    /**
     * @return string
     */
    public function getBasketId(): string
    {
        $basketId = $this->getData(self::BASKET_ID);

        return is_scalar($basketId) ? (string)$basketId : '';
    }

    /**
     * @param string|null $qrCode
     * @return BasketInformationResponse
     */
    public function setQrCode(?string $qrCode): BasketInformationResponse
    {
        return $this->setData(self::QR_CODE, $qrCode);
    }

    /**
     * @return string|null
     */
    public function getQrCode(): ?string
    {
        $qrCode = $this->getData(self::QR_CODE);

        return is_scalar($qrCode) ? (string)$qrCode : null;
    }

    /**
     * @param string|null $deepLink
     * @return BasketInformationResponse
     */
    public function setDeepLink(?string $deepLink): BasketInformationResponse
    {
        return $this->setData(self::DEEP_LINK, $deepLink);
    }

    /**
     * @return string|null
     */
    public function getDeepLink(): ?string
    {
        $deepLink = $this->getData(self::DEEP_LINK);

        return is_scalar($deepLink) ? (string)$deepLink : null;
    }

    /**
     * @param string|null $deepLinkHms
     * @return BasketInformationResponse
     */
    public function setDeepLinkHms(?string $deepLinkHms): BasketInformationResponse
    {
        return $this->setData(self::DEEP_LINK_HMS, $deepLinkHms);
    }

    /**
     * @return string|null
     */
    public function getDeepLinkHms(): ?string
    {
        $deepLinkHms = $this->getData(self::DEEP_LINK_HMS);

        return is_scalar($deepLinkHms) ? (string)$deepLinkHms : null;
    }
}
