<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Data\RuleLabel;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection as CouponCollection;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class QuoteToBasketPromoCodesDataConverter implements QuoteToBasketDataConverterInterface
{
    public function __construct(
        private readonly CouponCollectionFactory $couponCollectionFactory,
        private readonly RuleRepositoryInterface $ruleRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function convert(Quote $quote): array
    {
        $promoCodesData = [];
        $appliedRuleIds = explode(',', (string)$quote->getAppliedRuleIds());
        foreach ($appliedRuleIds as $appliedRuleId) {
            try {
                $rule = $this->ruleRepository->getById((int)$appliedRuleId);
            } catch (NoSuchEntityException | LocalizedException $e) {
                $this->logger->error($e->getMessage());

                continue;
            }

            // @phpstan-ignore-next-line
            $couponCode = (string)$quote->getCouponCode();
            if ((string)$rule->getCouponType() === RuleInterface::COUPON_TYPE_SPECIFIC_COUPON
                && $couponCode
                && $coupon = $this->getCouponByCode($couponCode, (int)$appliedRuleId)
            ) {
                $promoCodesData[] = [
                    'name' => $this->getRuleLabel($rule, (int)$quote->getStoreId()),
                    'promo_code_value' => (string)$coupon->getCode()
                ];
            } else {
                $promoCodesData[] = [
                    'name' => $this->getRuleLabel($rule, (int)$quote->getStoreId()),
                    'promo_code_value' => __('No Coupon is required.')->render()
                ];
            }
        }

        return $promoCodesData;
    }

    private function getRuleLabel(RuleInterface $rule, int $storeId): string
    {
        $ruleLabel = null;
        $labels = $rule->getStoreLabels();

        if ($labels) {
            /** @var RuleLabel $label */
            foreach ($labels as $label) {
                if ($label->getStoreId() === $storeId) {
                    $ruleLabel = $label->getStoreLabel();
                    break;
                }
            }
        }

        return (string)($ruleLabel ?? $rule->getName());
    }

    private function getCouponByCode(string $couponCode, int $ruleId): ?Coupon
    {
        /** @var CouponCollection $couponCollection */
        $couponCollection = $this->couponCollectionFactory->create();
        $couponCollection->addFieldToFilter(Coupon::KEY_CODE, ['eq' => $couponCode])
            ->addFieldToFilter(Coupon::KEY_RULE_ID, ['eq' => $ruleId])
            ->load();

        $coupon = null;
        if ($couponCollection->count()) {
            $coupon = $couponCollection->getFirstItem();
        }

        return ($coupon instanceof Coupon && (int)$coupon->getRuleId() === $ruleId) ? $coupon : null;
    }
}
