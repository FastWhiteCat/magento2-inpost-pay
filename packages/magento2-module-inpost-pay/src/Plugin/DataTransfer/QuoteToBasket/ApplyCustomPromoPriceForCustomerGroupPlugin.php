<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Provider\Config\OmnibusConfigProvider;
use InPost\InPostPay\Provider\Product\CustomProductPromoPriceProvider;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasket\QuoteToBasketProductsDataTransfer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class ApplyCustomPromoPriceForCustomerGroupPlugin
{
    /**
     * @param OmnibusConfigProvider $configProvider
     * @param CustomProductPromoPriceProvider $customProductPromoPriceProvider
     */
    public function __construct(
        private readonly OmnibusConfigProvider $configProvider,
        private readonly CustomProductPromoPriceProvider $customProductPromoPriceProvider,
    ) {
    }

    /**
     * @param QuoteToBasketProductsDataTransfer $subject
     * @param $result
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer( //@phpstan-ignore-line
        QuoteToBasketProductsDataTransfer $subject,
        $result,
        Quote $quote,
        BasketInterface $basket
    ): void {
        $customPromoPriceAttribute = $this->configProvider->getCustomProductPromoPriceAttributeCode();
        $customerGroups = $this->configProvider->getCustomProductPromoPriceCustomerGroups();

        if (!$this->configProvider->isCustomPromoPriceForSpecificCustomerGroupEnabled()
            || $customPromoPriceAttribute === null
            || !in_array($quote->getCustomerGroupId(), $customerGroups)
        ) {
            return;
        }

        foreach ($basket->getProducts() as $inPostPayProduct) {
            try {
                $customPromoPrice = $this->customProductPromoPriceProvider->getCustomPromoPrice(
                    $inPostPayProduct->getEan(),
                    $customPromoPriceAttribute
                );
            } catch (NoSuchEntityException $e) {
                $customPromoPrice = null;
            }

            if ($customPromoPrice) {
                $inPostPayProduct->setPromoPrice($customPromoPrice);
            }
        }
    }
}
