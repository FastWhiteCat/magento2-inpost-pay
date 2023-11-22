<?php

namespace InPost\InPostPay\Api\Data;

interface BasketInformationResponseInterface
{
    public const BASKET_ID = 'basket_id';
    public const QR_CODE = 'qr_code';
    public const DEEP_LINK = 'deep_link';
    public const DEEP_LINK_HMS = 'deep_link_hms';

    /**
     * @param string $basketId
     * @return BasketInformationResponseInterface
     */
    public function setBasketId(string $basketId): BasketInformationResponseInterface;

    /**
     * @return string
     */
    public function getBasketId(): string;

    /**
     * @param string|null $qrCode
     * @return BasketInformationResponseInterface
     */
    public function setQrCode(?string $qrCode): BasketInformationResponseInterface;

    /**
     * @return string|null
     */
    public function getQrCode(): ?string;

    /**
     * @param string|null $deepLink
     * @return BasketInformationResponseInterface
     */
    public function setDeepLink(?string $deepLink): BasketInformationResponseInterface;

    /**
     * @return string|null
     */
    public function getDeepLink(): ?string;

    /**
     * @param string|null $deepLinkHms
     * @return BasketInformationResponseInterface
     */
    public function setDeepLinkHms(?string $deepLinkHms): BasketInformationResponseInterface;

    /**
     * @return string|null
     */
    public function getDeepLinkHms(): ?string;
}
