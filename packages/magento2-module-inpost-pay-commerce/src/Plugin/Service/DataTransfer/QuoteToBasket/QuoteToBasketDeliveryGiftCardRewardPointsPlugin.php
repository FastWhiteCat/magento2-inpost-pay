<?php

declare(strict_types=1);

namespace InPost\InPostPayCommerce\Plugin\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasket\QuoteToBasketDeliveryDataTransfer;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class QuoteToBasketDeliveryGiftCardRewardPointsPlugin
{
    public function __construct(
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param QuoteToBasketDeliveryDataTransfer $subject
     * @param $result
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer(
        QuoteToBasketDeliveryDataTransfer $subject,
        $result,
        Quote $quote,
        BasketInterface $basket
    ): void {
        if (!$this->hasGiftCardOrRewardPoints($quote)) {
            return;
        }

        $this->logger->warning(
            'Gift card or reward points applied to cart. Setting empty delivery.'
        );

        $this->createBasketNotice->execute(
            (string)$basket->getBasketId(),
            InPostPayBasketNoticeInterface::ATTENTION,
            __(
                'Gift cards and reward points may only be used in the store checkout.'
                 . ' ' . 'They are not supported in InPost Pay Mobile App.'
            )->render()
        );

        $basket->setDelivery([]);
    }

    private function hasGiftCardOrRewardPoints(Quote $quote): bool
    {
        $giftCardsAmount = $quote->getData('gift_cards_amount_used');
        $rewardCurrencyAmount = $quote->getData('reward_currency_amount');

        $hasGiftCard = is_scalar($giftCardsAmount) && (float)$giftCardsAmount > 0.00;
        $hasRewardPoints = is_scalar($rewardCurrencyAmount) && (float)$rewardCurrencyAmount > 0.00;

        return $hasGiftCard || $hasRewardPoints;
    }
}
