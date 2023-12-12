<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\OrderProcessorInterface;
use InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder\OrderToInPostOrderDataTransfer;
use InPost\InPostPay\Validator\OrderValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Base64Json as Base64JsonSerializer;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderCreate implements OrderCreateInterface
{
    private const REQUEST_PREFIX = 'ORDER_REQUEST';
    private const RESPONSE_PREFIX = 'ORDER_RESPONSE';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly JsonSerializer $jsonSerializer,
        private readonly Base64JsonSerializer $base64JsonSerializer,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly OrderValidator $orderValidator,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly OrderToInPostOrderDataTransfer $orderToInPostOrderDataTransfer,
        private readonly OrderInterfaceFactory $orderFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param OrderDetailsInterface $orderDetails
     * @param AccountInfoInterface $accountInfo
     * @param DeliveryInterface $delivery
     * @param AcceptedConsentInterface[] $consents
     * @param InvoiceDetailsInterface|null $invoiceDetails
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function execute(
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): OrderInterface
    {
        try {
            $requestParams = (array)$this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
            $this->createRequestDebugLog(self::REQUEST_PREFIX,  'Order creating...', $requestParams);
            $basketId = $orderDetails->getBasketId();
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId($basketId);
            $quote = $this->cartRepository->get($inPostPayQuote->getQuoteId());
            if ($quote instanceof Quote && $quote->getId()) {
                $inPostOrder = $this->combineInPostOrder(
                    $orderDetails,
                    $accountInfo,
                    $delivery,
                    $consents,
                    $invoiceDetails
                );
                $this->validate($quote, $inPostPayQuote, $inPostOrder);

                $order = $this->createOrderFromQuote($quote, $inPostOrder);

                return $this->prepareInPostOrderFromMagentoOrder($order);
            } else {
                throw new LocalizedException(__('Quote not found.'));
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new LocalizedException(__('Order could not be created. Reason: %1', $e->getMessage()));
        }
    }

    private function combineInPostOrder(
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): OrderInterface {
        /** @var OrderInterface $preValidatedInPostOrder */
        $inPostOrder = $this->orderFactory->create();
        $inPostOrder->setOrderDetails($orderDetails);
        $inPostOrder->setAccountInfo($accountInfo);
        $inPostOrder->setDelivery($delivery);
        $inPostOrder->setConsents($consents);
        $inPostOrder->setInvoiceDetails($invoiceDetails);

        return $inPostOrder;
    }

    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     */
    private function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        $this->orderValidator->validate($quote, $inPostPayQuote, $inPostOrder);
        $this->createRequestDebugLog(self::REQUEST_PREFIX,  'Order data valid.');
    }

    private function createOrderFromQuote(Quote $quote, OrderInterface $inPostOrder): Order
    {
        $order = $this->orderProcessor->execute($quote, $inPostOrder);

        $this->createRequestDebugLog(
            self::REQUEST_PREFIX,
            sprintf('Order Created: #%s', (string)$order->getIncrementId())
        );

        return $order;
    }

    private function prepareInPostOrderFromMagentoOrder(Order $order): OrderInterface
    {
        /** @var OrderInterface $inPostOrder */
        $inPostOrder = $this->orderFactory->create();
        $this->orderToInPostOrderDataTransfer->transfer($order, $inPostOrder);
        $this->createRequestDebugLog(
            self::RESPONSE_PREFIX,
            sprintf('InPost Order prepared for Basket ID: %s', $inPostOrder->getOrderDetails()->getBasketId()),
            $inPostOrder->getData()
        );

        return $inPostOrder;
    }

    private function createRequestDebugLog(string $logPrefix, string $message, array $data = []): void
    {
        $serializedData = ($data) ? $this->base64JsonSerializer->serialize($data) : '';
        $dataLabel = ($logPrefix === self::REQUEST_PREFIX) ? 'Payload' : 'Response';
        $this->logger->debug(sprintf('%s: %s %s: %s', $logPrefix, $message, $dataLabel, $serializedData));
    }
}
