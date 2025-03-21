<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Controller\Adminhtml\CheckoutAgreementController;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterfaceFactory;
use InPost\InPostPay\Api\InPostPayCheckoutAgreementRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class Edit extends CheckoutAgreementController implements HttpGetActionInterface
{
    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository
     * @param InPostPayCheckoutAgreementInterfaceFactory $inPostPayCheckoutAgreementInterfaceFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository,
        private readonly InPostPayCheckoutAgreementInterfaceFactory $inPostPayCheckoutAgreementInterfaceFactory
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $agreementId = $this->getRequest()->getParam(InPostPayCheckoutAgreementInterface::AGREEMENT_ID);
        $agreementId = (is_scalar($agreementId)) ? (int)$agreementId : null;

        try {
            $checkoutAgreement = $this->inPostPayCheckoutAgreementRepository->get((int)$agreementId);
        } catch (LocalizedException $e) {
            $checkoutAgreement = $this->inPostPayCheckoutAgreementInterfaceFactory->create();
        }

        $isNew = $checkoutAgreement->getAgreementId() === null;

        /** @var Page $resultPage */
        $resultPage = $this->pageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            __('InPost Pay Checkout Agreement')->render(),
            $isNew ? __('New')->render() : __('Edit')->render()
        );
        $resultPage->getConfig()->getTitle()->prepend(__('InPost Pay Checkout Agreement')->render());

        if (!$isNew) {
            $title = __('Edit InPost Pay Checkout Agreement %1', $agreementId)->render();
        } else {
            $title = __('New InPost Pay Checkout Agreement')->render();
        }

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
