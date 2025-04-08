<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Bestsellers;

use Exception;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterfaceFactory;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @param InPostPayBestsellerProductInterfaceFactory $inPostPayBestsellerProductFactory
     * @param InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly InPostPayBestsellerProductInterfaceFactory $inPostPayBestsellerProductFactory,
        private readonly InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
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
            $bestsellerProduct = $this->createOrUpdateBestsellerProduct($data, (int)$bestsellerProductId);
            $this->messageManager->addSuccessMessage(
                __(
                    'You have saved the InPost Pay Bestseller Product [ID:%1].',
                    $bestsellerProduct->getBestsellerProductId()
                )->render()
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

    /**
     * @param array $data
     * @param int $bestsellerProductId
     * @return InPostPayBestsellerProductInterface
     * @throws CouldNotSaveException
     */
    private function createOrUpdateBestsellerProduct(
        array $data,
        int $bestsellerProductId
    ): InPostPayBestsellerProductInterface {
        try {
            $bestsellerProduct = $this->inPostPayBestsellerProductRepository->get($bestsellerProductId);
            $bestsellerProduct->setBestsellerProductId($bestsellerProductId);
        } catch (NoSuchEntityException $e) {
            /** @var InPostPayBestsellerProductInterface $bestsellerProduct */
            $bestsellerProduct = $this->inPostPayBestsellerProductFactory->create();
        }

        $sku = (string)($data[InPostPayBestsellerProductInterface::SKU] ?? '');
        $websiteId = (int)($data[InPostPayBestsellerProductInterface::WEBSITE_ID] ?? 0);
        $availableStartDate = (string)($data[InPostPayBestsellerProductInterface::AVAILABLE_START_DATE] ?? '');
        $availableEndDate = (string)($data[InPostPayBestsellerProductInterface::AVAILABLE_END_DATE] ?? '');

        $bestsellerProduct->setSku($sku);
        $bestsellerProduct->setWebsiteId($websiteId);
        $bestsellerProduct->setSynchronizedAt(null);
        $bestsellerProduct->setAvailableStartDate(!empty($availableStartDate) ? $availableStartDate : null);
        $bestsellerProduct->setAvailableEndDate(!empty($availableEndDate) ? $availableEndDate : null);

        return $this->inPostPayBestsellerProductRepository->save($bestsellerProduct);
    }
}
