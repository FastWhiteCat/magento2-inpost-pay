<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterfaceFactory;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Edit extends BestsellersController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::bestseller_products';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
     * @param InPostPayBestsellerProductInterfaceFactory $bestsellerProductInterfaceFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository,
        private readonly InPostPayBestsellerProductInterfaceFactory $bestsellerProductInterfaceFactory
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $bestsellerProductId = $this->getRequest()->getParam(
            InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID
        );
        $bestsellerProductId = (is_scalar($bestsellerProductId)) ? (int)$bestsellerProductId : 0;

        try {
            $bestsellerProduct = $this->inPostPayBestsellerProductRepository->get($bestsellerProductId);
        } catch (LocalizedException $e) {
            $bestsellerProduct = $this->bestsellerProductInterfaceFactory->create();
        }

        $isNew = $bestsellerProduct->getBestsellerProductId() !== null;

        /** @var Page $resultPage */
        $resultPage = $this->pageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            __('InPost Pay Bestseller Product')->render(),
            $isNew ? __('New')->render() : __('Edit')->render()
        );
        $resultPage->getConfig()->getTitle()->prepend(__('InPost Pay Bestseller Product')->render());

        $editTitle = __('Edit InPost Pay Bestseller Product %1', $bestsellerProductId)->render();
        $newTitle = __('New InPost Pay Bestseller Product')->render();
        $resultPage->getConfig()->getTitle()->prepend($isNew ? $editTitle : $newTitle);

        return $resultPage;
    }
}
