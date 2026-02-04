<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Observer\CheckoutAgreement\ValidateSubAgreementUsageInParentAgreementsEventObserver
    as Obsrv;

class ValidateSubAgreementUsageInParentAgreementsBeforeDeleteEventObserver extends Obsrv implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $agreement = $observer->getEvent()->getData(InPostPayCheckoutAgreementInterface::ENTITY_NAME);

        if ($agreement instanceof InPostPayCheckoutAgreementInterface) {
            $this->validateSubAgreementUsageInParentAgreements($agreement, true);
        }
    }
}
