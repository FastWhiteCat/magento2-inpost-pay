<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterfaceFactory;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\OrderProcessorInterface;
use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Exception\OrderNotFoundException;
use InPost\InPostPay\Service\DataTransfer\OrderToInPostOrderDataTransfer;
use InPost\InPostPay\Validator\OrderValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderCreate implements OrderCreateInterface
{
    private const REQUEST_PREFIX = 'ORDER_CREATE_REQUEST';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
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
     * @throws InPostPayBadRequestException
     * @throws InPostPayAuthorizationException
     * @throws OrderNotFoundException
     * @throws InPostPayInternalException
     */
    public function execute(
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): OrderInterface {
        try {
            $this->createRequestDebugLog('Creating order...');
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
                throw new NoSuchEntityException(__('Quote not found.'));
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw new OrderNotFoundException();
        } catch (InPostPayAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new InPostPayBadRequestException();
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            throw new InPostPayInternalException();
        }
    }

    private function combineInPostOrder(
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): OrderInterface {
        /** @var OrderInterface $inPostOrder */
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
        $this->createRequestDebugLog('Order data valid.');
    }

    private function createOrderFromQuote(Quote $quote, OrderInterface $inPostOrder): Order
    {
        $order = $this->orderProcessor->execute($quote, $inPostOrder);

        $this->createRequestDebugLog(sprintf('Order Created: #%s', (string)$order->getIncrementId()));

        return $order;
    }

    private function prepareInPostOrderFromMagentoOrder(Order $order): OrderInterface
    {
        /** @var OrderInterface $inPostOrder */
        $inPostOrder = $this->orderFactory->create();
        $this->orderToInPostOrderDataTransfer->transfer($order, $inPostOrder);

        return $inPostOrder;
    }

    private function createRequestDebugLog(string $message): void
    {
        $this->logger->debug(sprintf('%s: %s', self::REQUEST_PREFIX, $message));
    }
}
