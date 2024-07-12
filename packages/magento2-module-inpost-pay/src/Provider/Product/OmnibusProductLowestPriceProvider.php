<?php
/**
 * Copyright © Fast White Cat S.A. All rights reserved.
 * See LICENSE_FASTWHITECAT for license details.
 */

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Provider\Config\OmnibusConfigProvider;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface;

class OmnibusProductLowestPriceProvider
{
    public const INPOST_PAY_OMNIBUS_LOWEST_PRICE_EVENT = 'inpost_pay_omnibus_product_lowest_price_calculate_after';

    private ?array $omnibusRuleIds = null;
    private ?string $omnibusLowestPriceAttributeCode = null;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param OmnibusConfigProvider $omnibusConfigProvider
     * @param TaxConfig $taxConfig
     * @param TaxCalculation $taxCalculation
     * @param PriceInterfaceFactory $priceFactory
     * @param EventManager $eventManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly OmnibusConfigProvider $omnibusConfigProvider,
        private readonly TaxConfig $taxConfig,
        private readonly TaxCalculation $taxCalculation,
        private readonly PriceInterfaceFactory $priceFactory,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Item $quoteItem
     * @return bool
     */
    public function canSendLowestPrice(Item $quoteItem): bool
    {
        $quoteAppliedRuleIds = $quoteItem->getQuote()->getAppliedRuleIds();
        $appliedRuleIds = $quoteItem->getAppliedRuleIds();

        if (is_string($appliedRuleIds)) {
            $appliedRuleIds = explode(',', $appliedRuleIds);
        }

        if (is_string($quoteAppliedRuleIds)) {
            $quoteAppliedRuleIds = explode(',', $quoteAppliedRuleIds);
        }

        foreach ($appliedRuleIds as $ruleId) {
            if (in_array((int)$ruleId, $this->getOmnibusRuleIds())
                && in_array((int)$ruleId, $quoteAppliedRuleIds)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Product $product
     * @return PriceInterface|null
     */
    public function getLowestPrice(Product $product): ?PriceInterface
    {
        $lowestPriceAttributeCode = $this->getOmnibusLowestPriceProductAttributeCode();

        if ($lowestPriceAttributeCode === null) {
            return null;
        }

        try {
            $product = $this->productRepository->get($product->getSku());
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }

        $lowestPriceAttribute = $product->getCustomAttribute($lowestPriceAttributeCode);
        $lowestPriceValue = $lowestPriceAttribute?->getValue() ?? null;

        if (!is_scalar($lowestPriceValue)) {
            return null;
        }

        $priceIncludesTax = $this->taxConfig->priceIncludesTax($product->getStore());
        $taxRate = $this->getProductTaxRate($product);
        $taxRateMultiplier = ($taxRate / 100) + 1;

        if ($priceIncludesTax) {
            $gross = (float)$lowestPriceValue;
            $net = $gross / $taxRateMultiplier;
        } else {
            $net = (float)$lowestPriceValue;
            $gross = $net * $taxRateMultiplier;
        }

        /** @var PriceInterface $lowestPrice */
        $lowestPrice = $this->priceFactory->create();
        $lowestPrice->setGross(round($gross, 2));
        $lowestPrice->setNet(round($net, 2));
        $lowestPrice->setVat(round($gross - $net, 2));

        $this->eventManager->dispatch(
            self::INPOST_PAY_OMNIBUS_LOWEST_PRICE_EVENT, [
                ProductInterface::LOWEST_PRICE => $lowestPrice
            ]
        );

        return $lowestPrice;
    }

    /**
     * @return array
     */
    private function getOmnibusRuleIds(): array
    {
        if ($this->omnibusRuleIds === null) {
            $this->omnibusRuleIds = $this->omnibusConfigProvider->getOmnibusCartPriceRuleIds();
        }

        return $this->omnibusRuleIds;
    }

    /**
     * @return string|null
     */
    private function getOmnibusLowestPriceProductAttributeCode(): ?string
    {
        if ($this->omnibusLowestPriceAttributeCode === null) {
            $lowestPriceAttributeCode = $this->omnibusConfigProvider->getOmnibusProductLowestPriceAttributeCode();

            if ($lowestPriceAttributeCode) {
                $this->omnibusLowestPriceAttributeCode = $lowestPriceAttributeCode;
            }
        }

        return $this->omnibusLowestPriceAttributeCode;
    }

    /**
     * @param Product $product
     * @return float
     */
    private function getProductTaxRate(Product $product): float
    {
        // @phpstan-ignore-next-line
        $productTaxClassId = $product->getTaxClassId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $product->getStore());
        // @phpstan-ignore-next-line
        $request->setProductClassId($productTaxClassId);

        return $this->taxCalculation->getRate($request);
    }
}
