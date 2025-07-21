<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class BestsellersController
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::bestseller_products';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected readonly PageFactory $pageFactory,
        protected readonly RedirectFactory $redirectFactory,
        protected readonly AuthorizationInterface $authorization,
        protected readonly RequestInterface $request,
        protected readonly ManagerInterface $messageManager
    ) {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    protected function initPage(Page $resultPage): Page
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)->addBreadcrumb(
            __('InPost Pay Bestseller Products')->render(),
            __('InPost Pay Bestseller Products')->render()
        );

        return $resultPage;
    }

    /**
     * @return Redirect
     */
    protected function handleNotAllowed(): Redirect
    {
        $this->messageManager->addErrorMessage(
            __('You are not allowed to view this page. Please contact Administrators.')->render()
        );

        return $this->redirectFactory->create()->setPath('admin/dashboard/index');
    }

    protected function isAllowed(): bool
    {
        return $this->authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
