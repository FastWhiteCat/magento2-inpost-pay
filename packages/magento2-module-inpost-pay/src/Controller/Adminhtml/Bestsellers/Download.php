<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use Exception;
use InPost\InPostPay\Service\BestsellerProduct\Download as DownloadService;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Download extends BestsellersController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::bestseller_products';

    /**
     * @param DownloadService $downloadService
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private readonly DownloadService $downloadService,
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();

        try {
            $this->downloadService->execute();
            $this->messageManager->addSuccessMessage(
                __('Bestseller Products configured in InPost Pay have been downloaded into Magento.')->render()
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __(
                    'There was a problem with downloading bestseller products from InPost Pay to Magento. Reason: %1',
                    $e->getMessage()
                )->render()
            );
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
