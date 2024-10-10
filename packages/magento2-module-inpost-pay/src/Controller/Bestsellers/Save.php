<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Bestsellers;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Service\BestsellerProductPersistorService;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends BestsellersController implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::bestseller_products';
    public const BESTSELLERS_KEY = 'inpostpay_bestsellers_key';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param DataPersistorInterface $dataPersistor
     * @param BestsellerProductPersistorService $bestsellerProductPersistorService
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly BestsellerProductPersistorService $bestsellerProductPersistorService
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        // @phpstan-ignore-next-line
        $data = $this->getRequest()->getPostValue();
        $bestsellerProductId = $data[InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID] ?? null;

        if (empty($data)) {
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->bestsellerProductPersistorService->execute($data);
            $this->messageManager->addSuccessMessage(
                __('You have saved the InPost Pay Bestseller Product.')->render()
            );
            $this->dataPersistor->clear(self::BESTSELLERS_KEY);

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving InPost Pay Bestseller Product.')->render()
            );
        }

        $this->dataPersistor->set(self::BESTSELLERS_KEY, $data);

        if ($bestsellerProductId) {
            return $resultRedirect->setPath(
                '*/*/edit',
                [InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID => $bestsellerProductId]
            );
        }

        return $resultRedirect->setPath('*/*/new');
    }
}
