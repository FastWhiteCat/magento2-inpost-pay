<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use http\Exception\InvalidArgumentException;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class OrderValidator
{
    /**
     * @var OrderValidatorInterface[]
     */
    private array $orderValidators = [];

    public function __construct(
        array $orderValidators
    ) {
        $this->initOrderValidators($orderValidators);
    }

    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return true on successful order quote validation
     * @throws LocalizedException
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): bool
    {
        foreach ($this->orderValidators as $orderValidator) {
            $orderValidator->validate($quote, $inPostPayQuote, $inPostOrder);
        }

        return true;
    }

    /**
     * @param array $orderValidators
     * @return void
     * @throws InvalidArgumentException
     */
    private function initOrderValidators(array $orderValidators): void
    {
        foreach ($orderValidators as $orderValidator) {
            if ($orderValidator instanceof OrderValidatorInterface) {
                $this->orderValidators[] = $orderValidator;
            }
        }

        if (empty($this->orderValidators)) {
            throw new InvalidArgumentException('There is no valid InPost Pay order validator injected.');
        }
    }
}
