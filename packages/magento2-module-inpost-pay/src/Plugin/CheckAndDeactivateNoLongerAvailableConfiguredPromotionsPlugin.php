<?php
namespace InPost\InPostPay\Plugin;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Exception\InvalidPromoCodeException;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderEvent;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Rule;
use InPost\InPostPay\Provider\PromotionsProvider;
use InPost\InPostPay\Validator\Quote\SalesRule\IsSalesRuleApplicableToQuoteValidator;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckAndDeactivateNoLongerAvailableConfiguredPromotionsPlugin
{
    public function __construct(
        private readonly PromotionsProvider $promotionsProvider,
        private readonly IsSalesRuleApplicableToQuoteValidator $isSalesRuleApplicableToQuoteValidator,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param QuoteToBasketDataTransfer $subject
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeTransfer(
        QuoteToBasketDataTransfer $subject,
        Quote $quote,
        BasketInterface $basket
    ): void {
        $coupon = (string)$quote->getCouponCode();
        $appliedRuleIds = explode(',', (string)$quote->getAppliedRuleIds());
        $configuredSalesRules = $this->promotionsProvider->getConfiguredSalesRules($quote->getCustomerGroupId());

        // @phpstan-ignore-next-line
        if (!empty($appliedRuleIds) && !empty($configuredSalesRules)) {
            try {
                $this->checkAndDeactivateNoLongerAvailableConfiguredPromotions(
                    $quote,
                    $basket,
                    $appliedRuleIds,
                    $configuredSalesRules
                );
            } catch (Throwable $e) {
                $this->logger->error(
                    sprintf(
                        'Could not check coupon [%s] for quote ID:%s: Reason: %s',
                        $coupon,
                        is_scalar($quote->getId()) ? (int)$quote->getId() : 0,
                        $e->getMessage()
                    )
                );
            }
        }
    }

    private function checkAndDeactivateNoLongerAvailableConfiguredPromotions(
        Quote $quote,
        BasketInterface $basket,
        array $appliedRuleIds,
        array $configuredSalesRules
    ): void {
        $configuredSalesRuleIds = array_keys($configuredSalesRules);
        $commonSalesRuleIds = array_intersect($appliedRuleIds, $configuredSalesRuleIds);

        foreach ($commonSalesRuleIds as $commonSalesRuleId) {
            $salesRule = $configuredSalesRules[$commonSalesRuleId] ?? null;

            if (!$salesRule instanceof Rule) {
                continue;
            }

            try {
                $this->isSalesRuleApplicableToQuoteValidator->validate($quote, $salesRule);
            } catch (InvalidPromoCodeException $e) {
                $this->deactivateCouponForQuote($quote, $salesRule, $e->getMessage());
                $this->createBasketNotice->execute(
                    (string)$basket->getBasketId(),
                    InPostPayBasketNoticeInterface::ATTENTION,
                    $e->getMessage()
                );
            }
        }
    }

    private function deactivateCouponForQuote(Quote $quote, Rule $salesRule, string $deactivationReason): void
    {
        $couponCode = $salesRule->getCouponCode();

        try {
            $quote->setCouponCode('');
            $quote->collectTotals();
            $quote->setData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->cartRepository->save($quote);

            $this->logger->debug(
                sprintf(
                    'Coupon [%s] has been deactivated for quote ID:%s: Reason: %s',
                    $couponCode,
                    is_scalar($quote->getId()) ? (int)$quote->getId() : 0,
                    $deactivationReason
                )
            );
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Could not deactivate coupon [%s] for quote ID:%s: Reason: %s',
                    $couponCode,
                    is_scalar($quote->getId()) ? (int)$quote->getId() : 0,
                    $e->getMessage()
                )
            );
        }
    }
}
