<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStoreInterface;

use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;

interface BasketInterface
{
    public const BASKET_ID = 'basket_id';
    public const SUMMARY = 'summary';
    public const DELIVERY = 'delivery';
    public const PROMO_CODES = 'promo_codes';
    public const PRODUCTS = 'products';
    public const RELATED_PRODUCTS = 'related_products';
    public const CONSENTS = 'consents';
    public const PROMOTIONS_AVAILABLE = 'promotions_available';
    public const MERCHANT_STORE = 'merchant_store';
    public const INPOST_DATE_FORMAT = 'Y-m-d\TH:i:s\Z';
    public const STATUS = 'status';

    /**
     * @return string|null
     */
    public function getBasketId(): ?string;

    /**
     * @param string|null $basketId
     * @return void
     */
    public function setBasketId(string|null $basketId): void;

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
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface[]
     */
    public function getDelivery(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface[] $deliveries
     * @return void
     */
    public function setDelivery(array $deliveries): void;

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

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStoreInterface|null
     */
    public function getMerchantStore(): ?MerchantStoreInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStoreInterface|null $merchantStore
     * @return void
     */
    public function setMerchantStore(?MerchantStoreInterface $merchantStore = null): void;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string|null $status
     * @return void
     */
    public function setStatus(string|null $status): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailableInterface[]
     */
    public function getPromotionsAvailable(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailableInterface[] $promotionsAvailable
     * @return void
     */
    public function setPromotionsAvailable(array $promotionsAvailable): void;
}
