<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\PaymentMethods;

use InPost\InPostPay\Service\SynchronizePaymentMethods;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Synchronize extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::inpostpay';

    public function __construct(
        Context $context,
        private readonly SynchronizePaymentMethods $synchronizePaymentMethods,
        private readonly JsonFactory $resultJsonFactory,
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $this->synchronizePaymentMethods->execute();
            $this->messageManager->addSuccessMessage(__('Payment methods synchronization complete.')->render());
        } catch (\Exception) {
            $this->messageManager->addErrorMessage(
                __('Error occurred in payment method synchronization.')->render()
            );
        }

        return $result->setData([]);
    }
}
