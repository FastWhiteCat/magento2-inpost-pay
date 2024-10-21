<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\MagentoBestsellerToInPostPayBestseller;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\DataTransfer\MagentoBestsellerToInPostPayBestsellerDataTransferInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class BestsellerPriceDataTransfer implements MagentoBestsellerToInPostPayBestsellerDataTransferInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param InPostPayBestsellerProductInterface $magentoBestsellerProduct
     * @param BestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws NoSuchEntityException
     */
    public function transfer(
        InPostPayBestsellerProductInterface $magentoBestsellerProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void {
        /** @var Product $product */
        $product = $this->productRepository->get($magentoBestsellerProduct->getSku());
        $price = $bestsellerProduct->getPrice();

        $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount();
        $finalPriceExclTax = DecimalCalculator::round((float)$finalPrice->getBaseAmount());
        $finalPriceInclTax = DecimalCalculator::round((float)$finalPrice->getValue());
        $finalPriceTaxAmount = DecimalCalculator::sub($finalPriceInclTax, $finalPriceExclTax);

        $price->setGross($finalPriceExclTax);
        $price->setNet($finalPriceInclTax);
        $price->setVat($finalPriceTaxAmount);

        $bestsellerProduct->setPrice($price);
        $bestsellerProduct->setCurrency($this->getWebsiteCurrencyCode($magentoBestsellerProduct->getWebsiteId()));
    }

    /**
     * @param int $websiteId
     * @return string
     */
    private function getWebsiteCurrencyCode(int $websiteId): string
    {
        try {
            /** @var Website $website */
            $website = $this->storeManager->getWebsite($websiteId);
            $store = $website->getDefaultStore();
            return $store->getCurrentCurrency()->getCurrencyCode();
        } catch (LocalizedException $e) {
            return BestsellerProductInterface::DEFAULT_CURRENCY;
        }
    }
}
