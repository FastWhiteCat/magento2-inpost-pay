<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Quote\SalesRule;

use InPost\InPostPay\Exception\InvalidPromoCodeException;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Utility as SalesRuleUtility;

class IsSalesRuleApplicableToQuoteValidator
{
    public function __construct(
        private readonly SalesRuleUtility $salesRuleUtility
    ) {
    }

    /**
     * @param Quote $quote
     * @param Rule $rule
     * @return void
     * @throws InvalidPromoCodeException
     */
    public function validate(Quote $quote, Rule $rule): void
    {
        $address = !$quote->isVirtual() ? $quote->getShippingAddress() : $quote->getBillingAddress();
        $coupon = $rule->getPrimaryCoupon();

        if (!$this->salesRuleUtility->canProcessRule($rule, $address)) {
            throw new InvalidPromoCodeException(
                __(
                    'Coupon code "%1" expired or that promotion is not applicable to this cart.',
                    (string)$coupon->getCode()
                )
            );
        }
    }
}
