<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use Exception;
use InPost\InPostPay\Exception\BestsellerProductsLimitReachedException;
use InPost\InPostPay\Exception\NotFullySuccessfulBestsellerProductUploadException;
use InPost\InPostPay\Service\BestsellerProduct\Upload as UploadService;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Upload extends BestsellersController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::bestseller_products';

    /**
     * @param UploadService $uploadService
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private readonly UploadService $uploadService,
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
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
            $this->uploadService->execute();
            $this->messageManager->addSuccessMessage(
                __('Bestseller Products configured in Magento have been uploaded into InPost Pay.')->render()
            );
        } catch (NotFullySuccessfulBestsellerProductUploadException $e) {
            $this->messageManager->addWarningMessage(__($e->getMessage())->render());
        } catch (BestsellerProductsLimitReachedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage())->render());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __(
                    'There was a problem with uploading bestseller products from Magento to InPost Pay. Reason: %1',
                    $e->getMessage()
                )->render()
            );
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
