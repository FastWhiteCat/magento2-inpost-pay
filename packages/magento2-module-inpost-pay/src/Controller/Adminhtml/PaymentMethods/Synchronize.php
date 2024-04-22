<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\PaymentMethods;

use InPost\InPostPay\Service\SynchronizePaymentMethods;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Synchronize extends Action
{
    const ADMIN_RESOURCE = 'InPost_InPostPay::inpostpay';

    public function __construct(
        Context $context,
        private readonly SynchronizePaymentMethods $synchronizePaymentMethods
    ) {
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $this->synchronizePaymentMethods->execute();
        } catch (\Exception) {
            $this->messageManager->addErrorMessage(__('Error occurred in payment method synchronization.'));
            return [];
        }

        $this->messageManager->addSuccessMessage(__('Payment methods synchronization complete.'));
        return [];
    }
}
