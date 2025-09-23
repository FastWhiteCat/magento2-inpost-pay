<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class PaymentTypeValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider
    ) {
    }

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        $orderPaymentType = $inPostOrder->getOrderDetails()->getPaymentType();

        if ($inPostOrder->getOrderDetails()->getBasketPrice()->getGross() === 0.00) {
            $acceptedPaymentTypes = [OrderDetailsInterface::FREE_ORDER];
        } else {
            $acceptedPaymentTypes = $this->iziApiConfigProvider->getAcceptedPaymentTypes();
        }

        if (!empty($acceptedPaymentTypes) && !in_array($orderPaymentType, $acceptedPaymentTypes)) {
            throw new LocalizedException(__('Payment type %1 is not acceptable by merchant.', $orderPaymentType));
        }
    }
}
