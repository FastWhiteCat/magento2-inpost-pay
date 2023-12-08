<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use Magento\Framework\DataObject;

class Basket extends DataObject implements BasketInterface
{
    /**
     * @param SummaryInterfaceFactory $summaryFactory
     * @param DeliveryInterfaceFactory $deliveryFactory
     * @param array $data
     */
    public function __construct(
        private readonly SummaryInterfaceFactory $summaryFactory,
        private readonly DeliveryInterfaceFactory $deliveryFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getBrowserId(): string
    {
        $browserId = $this->getData(self::BROWSER_ID);

        return is_scalar($browserId) ? (string)$browserId : '';
    }

    /**
     * @param string $browserId
     * @return void
     */
    public function setBrowserId(string $browserId): void
    {
        $this->setData(self::BROWSER_ID, $browserId);
    }

    /**
     * @return SummaryInterface
     */
    public function getSummary(): SummaryInterface
    {
        $summary = $this->getData(self::SUMMARY);

        if ($summary instanceof SummaryInterface) {
            return $summary;
        }

        return $this->summaryFactory->create();
    }

    /**
     * @param SummaryInterface $summary
     * @return void
     */
    public function setSummary(SummaryInterface $summary): void
    {
        $this->setData(self::SUMMARY, $summary);
    }

    /**
     * @return DeliveryInterface
     */
    public function getDelivery(): DeliveryInterface
    {
        $delivery = $this->getData(self::DELIVERY);

        if ($delivery instanceof DeliveryInterface) {
            return $delivery;
        }

        return $this->deliveryFactory->create();
    }

    /**
     * @param DeliveryInterface $delivery
     * @return void
     */
    public function setDelivery(DeliveryInterface $delivery): void
    {
        $this->setData(self::DELIVERY, $delivery);
    }

    /**
     * @return PromoCodeInterface[]
     */
    public function getPromoCodes(): array
    {
        $promoCodes = $this->getData(self::PROMO_CODES);

        return is_array($promoCodes) ? $promoCodes : [];
    }

    /**
     * @param PromoCodeInterface[] $promoCodes
     * @return void
     */
    public function setPromoCodes(array $promoCodes): void
    {
        $this->setData(self::PROMO_CODES, $promoCodes);
    }

    /**
     * @return ProductInterface[]
     */
    public function getProducts(): array
    {
        $products = $this->getData(self::PRODUCTS);

        return is_array($products) ? $products : [];
    }

    /**
     * @param ProductInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void
    {
        $this->setData(self::PRODUCTS, $products);
    }

    /**
     * @return ProductInterface[]
     */
    public function getRelatedProducts(): array
    {
        $relatedProducts = $this->getData(self::RELATED_PRODUCTS);

        return is_array($relatedProducts) ? $relatedProducts : [];
    }

    /**
     * @param ProductInterface[] $relatedProducts
     * @return void
     */
    public function setRelatedProducts(array $relatedProducts): void
    {
        $this->setData(self::RELATED_PRODUCTS, $relatedProducts);
    }

    /**
     * @return ConsentInterface[]
     */
    public function getConsents(): array
    {
        $consents = $this->getData(self::CONSENTS);

        return is_array($consents) ? $consents : [];
    }

    /**
     * @param ConsentInterface[] $consents
     * @return void
     */
    public function setConsents(array $consents): void
    {
        $this->setData(self::CONSENTS, $consents);
    }
}
