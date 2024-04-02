<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\OrderComplete;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Get implements HttpGetActionInterface
{
    private readonly RequestInterface $request;

    public function __construct(
        Context $context,
        private readonly CheckoutSession $checkoutSession,
        private readonly JsonFactory $jsonFactory,
        private readonly UrlInterface $urlBuilder,
        private readonly InPostPayQuote $inPostPayQuote,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly LoggerInterface $logger
    ) {
        $this->request = $context->getRequest();
    }

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        $data = [];

        try {
            $basketId = is_scalar($this->request->getParam('basketId')) ?
                (string)$this->request->getParam('basketId') :
                '';

            if ($basketId) {
                $inPostPayData = $this->inPostPayQuote->getCartVersionAndOrderId($basketId);

                if (empty($inPostPayData)) {
                    $inPostPayData = $this->getInPostPayOrderDataByBasketId($basketId);
                }

                if (isset($inPostPayData[InPostPayOrderInterface::ORDER_ID])) {
                    $data = [
                        'action' => 'redirect',
                        'redirect' => $this->urlBuilder->getUrl('checkout/onepage/success/')
                    ];

                    $order = $this->orderRepository->get($inPostPayData[InPostPayOrderInterface::ORDER_ID]);

                    $this->checkoutSession->setLastQuoteId($order->getQuoteId());
                    $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                    $this->checkoutSession->setLastOrderId($order->getEntityId());
                    $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
                    $this->checkoutSession->setLastOrderStatus($order->getStatus());
                }

                $cartVersion = (string)($inPostPayData[InPostPayQuoteInterface::CART_VERSION] ?? '');
                $data[InPostPayQuoteInterface::CART_VERSION] = $cartVersion;
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }

        return $this->jsonFactory->create()->setData($data);
    }

    private function getInPostPayOrderDataByBasketId(string $basketId): array
    {
        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByBasketId($basketId);
            $inPostPayData[InPostPayOrderInterface::ORDER_ID] = $inPostPayOrder->getOrderId();
        } catch (NoSuchEntityException | LocalizedException $e) {
            $inPostPayData = [];
        }

        return $inPostPayData;
    }
}
