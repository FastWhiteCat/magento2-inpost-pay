<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\InPostPay\Validator\OrderRestrictionsValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class RestrictionsValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly OrderRestrictionsValidator $orderRestrictionsValidator,
        private readonly LoggerInterface$logger
    ) {
    }

    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        try {
            $this->orderRestrictionsValidator->validate($quote, $inPostOrder, true);
        } catch (InPostPayRestrictedProductException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }
}
