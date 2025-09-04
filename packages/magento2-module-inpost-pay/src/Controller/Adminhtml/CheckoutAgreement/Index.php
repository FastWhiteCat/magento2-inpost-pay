<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\CheckoutAgreement;

use InPost\InPostPay\Controller\Adminhtml\CheckoutAgreementController;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends CheckoutAgreementController implements HttpGetActionInterface
{
    public function execute(): ResultInterface
    {
        if (!$this->isAllowed()) {
            return $this->handleNotAllowed();
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("InPost Pay Terms And Conditions List")->render());

        return $resultPage;
    }
}
