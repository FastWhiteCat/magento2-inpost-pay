<?php

declare(strict_types=1);

namespace InPost\InPostPayCommerce\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPayCommerce\Provider\Quote\Total\GiftWrappingTotalProvider;

class QuoteGiftWrappingDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly GiftWrappingTotalProvider $giftWrappingTotalProvider
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $basketBasePrice = $basket->getSummary()->getBasketBasePrice();
        $basketPromoPrice = $basket->getSummary()->getBasketPromoPrice();
        $basketFinalPrice = $basket->getSummary()->getBasketFinalPrice();

        $totals = $quote->getShippingAddress()->getTotals();
        $giftWrappingTotal = $totals['giftwrapping'] ?? null;

        if ($giftWrappingTotal instanceof Total) {
            $originalGross = $basketBasePrice->getGross();
            $this->updateBasketPriceWithGiftWrappingTotal($basketBasePrice, $giftWrappingTotal);
            $this->updateBasketPriceWithGiftWrappingTotal($basketPromoPrice, $giftWrappingTotal);
            $this->updateBasketPriceWithGiftWrappingTotal($basketFinalPrice, $giftWrappingTotal);

            if ($basketBasePrice->getGross() > $originalGross) {
                $this->addGiftWrappingBasketNotice(
                    (string)$basket->getBasketId(),
                    __('Final price includes additional cost of gift wrapping.')->render()
                );
            }
        }
    }

    private function updateBasketPriceWithGiftWrappingTotal(
        PriceInterface $basketBasePrice,
        Total $giftWrappingTotal
    ): void {
        $originalGross = $basketBasePrice->getGross();
        $originalNet = $basketBasePrice->getNet();
        $originalVat = $basketBasePrice->getVat();

        $basketBasePrice->setGross(
            DecimalCalculator::add(
                $originalGross,
                $this->giftWrappingTotalProvider->getGiftWrappingTotalGross($giftWrappingTotal)
            )
        );
        $basketBasePrice->setNet(
            DecimalCalculator::add(
                $originalNet,
                $this->giftWrappingTotalProvider->getGiftWrappingTotalNet($giftWrappingTotal)
            )
        );
        $basketBasePrice->setVat(
            DecimalCalculator::add(
                $originalVat,
                $this->giftWrappingTotalProvider->getGiftWrappingTotalTaxAmount($giftWrappingTotal)
            )
        );
    }

    private function addGiftWrappingBasketNotice(string $basketId, string $message): void
    {
        $this->createBasketNotice->execute(
            $basketId,
            InPostPayBasketNoticeInterface::ATTENTION,
            $message
        );
    }
}
