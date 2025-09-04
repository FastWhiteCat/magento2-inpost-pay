<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint;

use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Registry\Order\Email\Sender\InPostPayOrderEmailSenderRegistry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;

class RegisterInPostPayOrderAccountEmailObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Order Create request';

    /**
     * @param InPostPayOrderInterfaceFactory $inPostPayOrderFactory
     * @param InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
     */
    public function __construct(
        private readonly InPostPayOrderInterfaceFactory $inPostPayOrderFactory,
        private readonly InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
    ) {
    }

    public function execute(Observer $observer): void
    {
        $accountInfo = $observer->getEvent()->getData(OrderInterface::ACCOUNT_INFO);

        if ($accountInfo instanceof AccountInfoInterface && $accountInfo->getMail()) {
            /**
             * Simplified object of InPost Pay Order is created and only filled with Account Mail
             * because that is the only required info to make sure an email copy to is sent to InPost Account Owner.
             * That object is not going to be saved.
             */
            /** @var InPostPayOrderInterface $inPostPayOrderSimplifiedObject */
            $inPostPayOrderSimplifiedObject = $this->inPostPayOrderFactory->create();
            $inPostPayOrderSimplifiedObject->setInPostPayAccountEmail($accountInfo->getMail());
            $this->inPostPayOrderEmailSenderRegistry->register($inPostPayOrderSimplifiedObject);
        }
    }
}
