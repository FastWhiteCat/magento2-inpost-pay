<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\CheckoutAgreement;

use Exception;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Controller\Adminhtml\CheckoutAgreementController;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Service\CheckoutAgreementPersistorService;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends CheckoutAgreementController implements HttpPostActionInterface
{
    public const INPOST_PAY_CHECKOUT_AGREEMENT_KEY = 'inpost_pay_checkout_agreement_key';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param DataPersistorInterface $dataPersistor
     * @param CheckoutAgreementPersistorService $checkoutAgreementPersistorService
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly CheckoutAgreementPersistorService $checkoutAgreementPersistorService
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        // @phpstan-ignore-next-line
        $agreementData = $this->getRequest()->getPostValue();
        $agreementId = $agreementData[InPostPayCheckoutAgreementInterface::AGREEMENT_ID] ?? null;

        if (empty($agreementData)) {
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->checkoutAgreementPersistorService->execute($agreementData);
            $this->messageManager->addSuccessMessage(
                __('You have saved the InPost Pay Checkout Agreement.')->render()
            );
            $this->dataPersistor->clear(self::INPOST_PAY_CHECKOUT_AGREEMENT_KEY);

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving InPost Pay Checkout Agreement.')->render()
            );
        }

        $this->dataPersistor->set(self::INPOST_PAY_CHECKOUT_AGREEMENT_KEY, $agreementData);

        if ($agreementId) {
            return $resultRedirect->setPath('*/*/edit', ['agreement_id' => $agreementId]);
        }

        return $resultRedirect->setPath('*/*/new');
    }
}
