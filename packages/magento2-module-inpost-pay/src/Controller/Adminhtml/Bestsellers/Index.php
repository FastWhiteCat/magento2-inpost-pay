<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends BestsellersController implements HttpGetActionInterface
{
    public function execute(): ResultInterface
    {
        if (!$this->isAllowed()) {
            return $this->handleNotAllowed();
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("InPost Pay Bestseller Products List")->render());

        return $resultPage;
    }
}
