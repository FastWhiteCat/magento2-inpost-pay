<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Basket extends DataObject implements BasketInterface, ExtensibleDataInterface
{
    /**
     * @param SummaryInterfaceFactory $summaryFactory
     * @param array $data
     */
    public function __construct(
        private readonly SummaryInterfaceFactory $summaryFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string|null
     */
    public function getBrowserId(): ?string
    {
        $browserId = $this->getData(self::BROWSER_ID);

        return is_scalar($browserId) ? (string)$browserId : null;
    }

    /**
     * @param string|null $browserId
     * @return void
     */
    public function setBrowserId(?string $browserId): void
    {
        $this->setData(self::BROWSER_ID, $browserId);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface
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
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface $summary
     * @return void
     */
    public function setSummary(SummaryInterface $summary): void
    {
        $this->setData(self::SUMMARY, $summary);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface[]
     */
    public function getDelivery(): array
    {
        $deliveries = $this->getData(self::DELIVERY);

        return is_array($deliveries) ? $deliveries : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface[] $deliveries
     * @return void
     */
    public function setDelivery(array $deliveries): void
    {
        $this->setData(self::DELIVERY, $deliveries);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface[]
     */
    public function getPromoCodes(): array
    {
        $promoCodes = $this->getData(self::PROMO_CODES);

        return is_array($promoCodes) ? $promoCodes : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface[] $promoCodes
     * @return void
     */
    public function setPromoCodes(array $promoCodes): void
    {
        $this->setData(self::PROMO_CODES, $promoCodes);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[]
     */
    public function getProducts(): array
    {
        $products = $this->getData(self::PRODUCTS);

        return is_array($products) ? $products : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void
    {
        $this->setData(self::PRODUCTS, $products);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[]
     */
    public function getRelatedProducts(): array
    {
        $relatedProducts = $this->getData(self::RELATED_PRODUCTS);

        return is_array($relatedProducts) ? $relatedProducts : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface[] $relatedProducts
     * @return void
     */
    public function setRelatedProducts(array $relatedProducts): void
    {
        $this->setData(self::RELATED_PRODUCTS, $relatedProducts);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface[]
     */
    public function getConsents(): array
    {
        $consents = $this->getData(self::CONSENTS);

        return is_array($consents) ? $consents : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface[] $consents
     * @return void
     */
    public function setConsents(array $consents): void
    {
        $this->setData(self::CONSENTS, $consents);
    }
}
