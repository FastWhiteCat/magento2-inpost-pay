<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Exception\BasketNotFoundException;
use InPost\InPostPay\Exception\InPostPayDigitalDeliveryException;
use InPost\InPostPay\Exception\OrderNotCreateException;
use InPost\InPostPay\Exception\QuoteItemOutOfStockException;
use InPost\InPostPay\Service\CreateBasketNotice;
use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
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
use InPost\InPostPay\Service\DataTransfer\OrderToInPostOrderDataTransfer;
use InPost\InPostPay\Validator\OrderValidator;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use InPost\InPostPay\Exception\QuoteChangedDuringOrderProcessingException;
use InPost\InPostPay\Service\UpdateInPostBasketEvent;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderCreate implements OrderCreateInterface
{
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
        private readonly EventManager $eventManager,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly UpdateInPostBasketEvent $updateInPostBasketEvent,
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
     * @throws BasketNotFoundException
     * @throws InPostPayInternalException
     * @throws OrderNotCreateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): OrderInterface {
        try {
            $this->eventManager->dispatch('izi_order_create_before', [
                OrderInterface::ORDER_DETAILS => $orderDetails,
                OrderInterface::ACCOUNT_INFO => $accountInfo,
                OrderInterface::DELIVERY => $delivery,
                OrderInterface::CONSENTS => $consents,
                OrderInterface::INVOICE_DETAILS => $invoiceDetails
            ]);

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

                $this->orderValidator->validate($quote, $inPostPayQuote, $inPostOrder);
                $order = $this->orderProcessor->execute($quote, $inPostPayQuote, $inPostOrder);
                $inPostOrder = $this->prepareInPostOrderFromMagentoOrder($order);

                $this->eventManager->dispatch(
                    'izi_order_create_after',
                    [
                        OrderCreateInterface::INPOST_ORDER => $inPostOrder
                    ]
                );

                return  $inPostOrder;
            } else {
                throw new NoSuchEntityException(__('Quote not found.'));
            }
        } catch (QuoteChangedDuringOrderProcessingException $e) {
            $this->addBasketNoticeError($orderDetails->getBasketId(), $e->getMessage());

            // @phpstan-ignore-next-line
            if (isset($quote) && $quote->getId()) {
                $this->forceBasketUpdate($quote);
            }

            throw new OrderNotCreateException(__($e->getMessage()));
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            $this->addBasketNoticeError($orderDetails->getBasketId(), $e->getMessage());
            throw new BasketNotFoundException();
        } catch (QuoteItemOutOfStockException | InPostPayDigitalDeliveryException $e) {
            $this->logger->error($e->getMessage());

            $this->addBasketNoticeError($orderDetails->getBasketId(), $e->getMessage());
            throw new OrderNotCreateException(__($e->getMessage()));
        } catch (InPostPayAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            $this->addBasketNoticeError($orderDetails->getBasketId(), $e->getMessage());
            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            $this->addBasketNoticeError($orderDetails->getBasketId(), $e->getMessage());
            throw new InPostPayBadRequestException();
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            $this->addBasketNoticeError($orderDetails->getBasketId(), $e->getMessage());
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

    private function prepareInPostOrderFromMagentoOrder(Order $order): OrderInterface
    {
        /** @var OrderInterface $inPostOrder */
        $inPostOrder = $this->orderFactory->create();
        $this->orderToInPostOrderDataTransfer->transfer($order, $inPostOrder);

        return $inPostOrder;
    }

    private function addBasketNoticeError(string $basketId, string $message): void
    {
        $this->createBasketNotice->execute(
            $basketId,
            InPostPayBasketNoticeInterface::ERROR,
            $message
        );
    }

    /**
     * @param Quote $quote
     * @return void
     * @throws BasketNotFoundException
     */
    private function forceBasketUpdate(Quote $quote): void
    {
        try {
            $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;
            /** @var Quote $quote */
            $quote = $this->cartRepository->get($quoteId);
            $this->updateInPostBasketEvent->execute($quote);
        } catch (NoSuchEntityException $e) {
            throw new BasketNotFoundException();
        }
    }
}
