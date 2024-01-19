<?php

declare(strict_types=1);

namespace InPost\Restrictions\Controller\Adminhtml\Rule;

use InPost\Restrictions\Controller\Adminhtml\RestrictionsController;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends RestrictionsController implements HttpGetActionInterface
{
    public function execute(): ResultInterface
    {
        if (!$this->isAllowed()) {
            return $this->handleNotAllowed();
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("InPost Restrictions Rules List")->render());

        return $resultPage;
    }
}
