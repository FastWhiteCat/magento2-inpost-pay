<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Rule\Condition;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Exception\OnlyInAppException;
use InPost\InPostPay\Registry\InPostPayMobileAppOrderRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule;

class IsInPostPayOrder extends AbstractCondition
{

    /**
     * @param Context $context
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param InPostPayMobileAppOrderRegistry $inPostPayMobileAppOrderRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly InPostPayMobileAppOrderRegistry $inPostPayMobileAppOrderRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions(): self
    {
        $attributes = [
            'is_inpost_pay_order' => __('Only orders placed with InPost Pay Mobile App')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return 'boolean';
    }

    /**
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'select';
    }

    /**
     * @return array[]
     */
    public function getValueSelectOptions(): array
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')]
        ];
    }

    /**
     * Validate if the quote is paired with InPost Pay and if that is a final ordering step if source is InPost Pay
     *
     * @param AbstractModel $model
     * @return bool
     * @throws OnlyInAppException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(AbstractModel $model)
    {
        $quote = $model->getQuote();
        $paymentMethod = null;
        $quoteId = 0;

        if ($quote instanceof Quote) {
            $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;
            $paymentMethod = $quote->getPayment()->getMethod();
        } elseif ($model->getQuoteId()) {
            $quoteId = (int)$model->getQuoteId();
        }

        if (!$quoteId || !$this->validateCouponType()) {
            return false;
        }

        try {
            $this->inPostPayQuoteRepository->getByQuoteId($quoteId);

            if ($this->inPostPayMobileAppOrderRegistry->isMobileAppQuote($quoteId)) {
                return true;
            }

            if ($paymentMethod === null || $paymentMethod === 'inpost_pay') {
                return true;
            }

            throw new OnlyInAppException(
                __('Discount You are using can only be applied to orders placed with InPost Pay Mobile App.'
                    . ' To complete You order with this discount use InPost Pay Mobile App'
                    . ' or edit You cart and disable that discount to complete order in browser.')
            );
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function validateCouponType(): bool
    {
        $couponType = null;
        $rule = $this->getData('rule');

        if ($rule instanceof Rule) {
            $couponType = (int)$rule->getCouponType();
        }

        return $couponType !== Rule::COUPON_TYPE_NO_COUPON;
    }
}
