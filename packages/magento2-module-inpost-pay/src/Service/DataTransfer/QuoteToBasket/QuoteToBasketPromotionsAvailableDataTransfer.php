<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailableInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailableInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Provider\PromotionsProvider;
use InPost\InPostPay\Validator\Quote\SalesRule\IsSalesRuleApplicableToQuoteValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Rule;

class QuoteToBasketPromotionsAvailableDataTransfer implements QuoteToBasketDataTransferInterface
{
    private const PROMOTION_LIMIT = 5;
    public function __construct(
        private readonly PromotionAvailableInterfaceFactory $promotionAvailableInterfaceFactory,
        private readonly DetailsInterfaceFactory $detailsInterfaceFactory,
        private readonly IsSalesRuleApplicableToQuoteValidator $isSalesRuleApplicableToQuoteValidator,
        private readonly PromotionsProvider $promotionsProvider
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $customerGroupId = (int)$quote->getCustomerGroupId();
        $promotions = [];
        $limit = 0;

        foreach ($this->promotionsProvider->getPromotions($customerGroupId) as $promotionData) {
            $ruleId = (int)($promotionData['rule_id'] ?? null);

            if (!$this->isRuleValidForQuote($ruleId, $quote)) {
                continue;
            }

            $limit++;
            if ($limit > self::PROMOTION_LIMIT) {
                break;
            }

            $details = $this->detailsInterfaceFactory->create();
            if ($promotionData[PromotionAvailableInterface::DETAILS]) {
                $link = $promotionData[PromotionAvailableInterface::DETAILS][DetailsInterface::LINK];
                $details->setLink((string)$link);
            }

            $priority = (int)$promotionData[PromotionAvailableInterface::PRIORITY];
            $priority = $priority === 0 ? 1 : $priority;
            $priority = min($priority, 5);

            /** @var PromotionAvailableInterface $promo $promo */
            $promo = $this->promotionAvailableInterfaceFactory->create();
            $promo->setType((string)$promotionData[PromotionAvailableInterface::TYPE]);
            $promo->setPromoCodeValue((string)$promotionData[PromotionAvailableInterface::PROMO_CODE_VALUE]);
            $promo->setDescription((string)$promotionData[PromotionAvailableInterface::DESCRIPTION]);
            $promo->setStartDate((string)$promotionData[PromotionAvailableInterface::START_DATE]);
            $promo->setEndDate((string)$promotionData[PromotionAvailableInterface::END_DATE]);
            $promo->setPriority($priority);
            $promo->setDetails($details);

            $promotions[] = $promo;
        }

        $basket->setPromotionsAvailable($promotions);
    }

    private function isRuleValidForQuote(int $ruleId, Quote $quote): bool
    {
        try {
            $rules = $this->promotionsProvider->getPromotionsList([$ruleId], (int)$quote->getCustomerGroupId());
            /** @var Rule|null $rule */
            $rule = $rules[$ruleId] ?? null;

            if ($rule === null) {
                return false;
            }

            $rule = $rules[$ruleId];
            $this->isSalesRuleApplicableToQuoteValidator->validate($quote, $rule);

            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
