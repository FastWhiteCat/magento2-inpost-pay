<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

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
use InPost\InPostPay\Exception\OrderNotFoundException;
use InPost\InPostPay\Service\DataTransfer\OrderToInPostOrderDataTransfer;
use InPost\InPostPay\Validator\OrderValidator;
use Magento\Framework\Event\ManagerInterface as EventManager;
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
                $order = $this->orderProcessor->execute($quote, $inPostOrder);
                $inPostOrder = $this->prepareInPostOrderFromMagentoOrder($order);

                $this->eventManager->dispatch(
                    'izi_order_create_after',
                    [OrderCreateInterface::INPOST_ORDER => $inPostOrder]
                );

                return  $inPostOrder;
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

    private function prepareInPostOrderFromMagentoOrder(Order $order): OrderInterface
    {
        /** @var OrderInterface $inPostOrder */
        $inPostOrder = $this->orderFactory->create();
        $this->orderToInPostOrderDataTransfer->transfer($order, $inPostOrder);

        return $inPostOrder;
    }
}
