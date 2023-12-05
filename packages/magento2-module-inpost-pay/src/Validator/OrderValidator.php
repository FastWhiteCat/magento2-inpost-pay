<?php
/**
 * Copyright © Fast White Cat S.A. All rights reserved.
 * See LICENSE_FASTWHITECAT for license details.
 */

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use http\Exception\InvalidArgumentException;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Model\Dto\Order as DtoOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;

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
     * @param DtoOrder $orderDto
     * @return true on successful order quote validation
     * @throws LocalizedException
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, DtoOrder $orderDto): bool
    {
        foreach ($this->orderValidators as $orderValidator) {
            $orderValidator->validate($quote, $inPostPayQuote, $orderDto);
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
