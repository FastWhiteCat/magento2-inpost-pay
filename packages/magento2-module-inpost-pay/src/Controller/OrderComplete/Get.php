<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\OrderComplete;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Controller\WidgetController;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Get extends WidgetController implements HttpGetActionInterface
{
    public const REDIRECT_RESULT_KEY = 'redirect';

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Validator $formKeyValidator
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     * @param UrlInterface $urlBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Validator $formKeyValidator,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        private readonly UrlInterface $urlBuilder,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
    ) {
        parent::__construct($context, $checkoutSession, $formKeyValidator, $jsonFactory, $logger);
    }

    public function execute(): Json
    {
        try {
            $basketBindingApiKey = $this->request->getParam(InPostPayQuoteInterface::BASKET_BINDING_API_KEY);
            $basketBindingApiKey = is_scalar($basketBindingApiKey) ? (string)$basketBindingApiKey : '';

            if ($basketBindingApiKey) {
                $inPostPayOrder = $this->inPostPayOrderRepository->getByBasketBindingApiKey($basketBindingApiKey);
                $order = $this->orderRepository->get($inPostPayOrder->getOrderId());

                $this->checkoutSession->setLastQuoteId($order->getQuoteId());
                $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                $this->checkoutSession->setLastOrderId($order->getEntityId());
                $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
                $this->checkoutSession->setLastOrderStatus($order->getStatus());

                $result = [
                    self::SUCCESS_RESULT_KEY => true,
                    self::REDIRECT_RESULT_KEY => $this->urlBuilder->getUrl('checkout/onepage/success/')
                ];
            } else {
                $result = [
                    self::SUCCESS_RESULT_KEY => false,
                    self::ERROR_RESULT_KEY => __('Empty Basket Binding API Key.')->render()
                ];
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());

            $result = [
                self::SUCCESS_RESULT_KEY => false,
                self::ERROR_RESULT_KEY => $e->getMessage()
            ];
        }

        return $this->jsonFactory->create()->setData($result);
    }
}
