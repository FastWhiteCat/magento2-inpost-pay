<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface;

use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;

interface BasketInterface
{
    public const BROWSER_ID = 'browser_id';
    public const SUMMARY = 'summary';
    public const DELIVERY = 'delivery';
    public const PROMO_CODES = 'promo_codes';
    public const PRODUCTS = 'products';
    public const RELATED_PRODUCTS = 'related_products';
    public const CONSENTS = 'consents';

    /**
     * @return string
     */
    public function getBrowserId(): string;

    /**
     * @param string $browserId
     * @return void
     */
    public function setBrowserId(string $browserId): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface
     */
    public function getSummary(): SummaryInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface $summary
     * @return void
     */
    public function setSummary(SummaryInterface $summary): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface
     */
    public function getDelivery(): DeliveryInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface $delivery
     * @return void
     */
    public function setDelivery(DeliveryInterface $delivery): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface[]
     */
    public function getPromoCodes(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface[] $promoCodes
     * @return void
     */
    public function setPromoCodes(array $promoCodes): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[]
     */
    public function getProducts(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[]
     */
    public function getRelatedProducts(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[] $relatedProducts
     * @return void
     */
    public function setRelatedProducts(array $relatedProducts): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface[]
     */
    public function getConsents(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface[] $consents
     * @return void
     */
    public function setConsents(array $consents): void;
}
