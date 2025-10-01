<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\CheckoutAgreement;

use Exception;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Api\InPostPayCheckoutAgreementRepositoryInterface;
use InPost\InPostPay\Controller\Adminhtml\CheckoutAgreementController;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Delete extends CheckoutAgreementController implements HttpGetActionInterface
{
    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();
        $agreementId = $this->getRequest()->getParam(InPostPayCheckoutAgreementInterface::AGREEMENT_ID);
        $agreementId = (is_scalar($agreementId)) ? (int)$agreementId : 0;

        try {
            $this->inPostPayCheckoutAgreementRepository->deleteById($agreementId);
            $this->messageManager->addSuccessMessage(
                __('You deleted Checkout Agreement with ID %1.', $agreementId)->render()
            );

            return $resultRedirect->setPath('*/*/');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath(
                '*/*/edit',
                [InPostPayCheckoutAgreementInterface::AGREEMENT_ID => $agreementId]
            );
        }
    }
}
