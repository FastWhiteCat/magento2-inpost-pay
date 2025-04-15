<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote\Payment;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote\Payment;

class RemoveZeroTotalCheckFromInPostPayPaymentEventObserver implements ObserverInterface
{

    public function execute(Observer $observer): void
    {
        $payment = $observer->getEvent()->getData('payment');
        $input = $observer->getEvent()->getData('input');

        if (!$payment instanceof Payment
            || !$input instanceof DataObject
            || $payment->getMethod() !== 'inpost_pay'
            || !is_array($input->getData('checks'))
        ) {
            return;
        }

        /** @var array $originalChecks */
        $originalChecks = $input->getData('checks');
        $newChecks = array_diff($originalChecks, [MethodInterface::CHECK_ZERO_TOTAL]);
        $input->setData('checks', $newChecks);
    }
}
