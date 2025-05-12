<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends BestsellersController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::bestseller_products';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    public function execute(): ResultInterface
    {
        if (!$this->isAllowed()) {
            return $this->handleNotAllowed();
        }

        return $this->resultForwardFactory->create()->forward('edit');
    }
}
