<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use Exception;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Delete extends BestsellersController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::bestseller_products';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();
        $bestsellerProductId = $this->getRequest()->getParam(
            InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID
        );
        $bestsellerProductId = (is_scalar($bestsellerProductId)) ? (int)$bestsellerProductId : 0;

        try {
            $this->inPostPayBestsellerProductRepository->deleteById($bestsellerProductId);
            $this->messageManager->addSuccessMessage(
                __('You have deleted InPost Pay Bestseller Product with ID %1.', $bestsellerProductId)->render()
            );

            return $resultRedirect->setPath('*/*/');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath(
                '*/*/index',
                [InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID => $bestsellerProductId]
            );
        }
    }
}
