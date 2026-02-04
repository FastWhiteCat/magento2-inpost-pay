<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class InitInPostPayOrderStep extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly InPostPayOrderInterfaceFactory $inPostPayOrderFactory,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @param Order $order
     * @param InPostOrderInterface $inPostOrder
     * @return void
     * @throws CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Order $order, InPostOrderInterface $inPostOrder): void
    {
        /** @var InPostPayOrderInterface $inPostPayOrder */
        $inPostPayOrder = $this->inPostPayOrderFactory->create();
        $orderId = (int)(is_scalar($order->getEntityId()) ? $order->getEntityId() : null);
        $inPostPayOrder->setOrderId($orderId);
        $inPostPayOrder->setPaymentType($inPostOrder->getOrderDetails()->getPaymentType());
        $inPostPayOrder->setBasketId($inPostOrder->getOrderDetails()->getBasketId());
        $inPostPayOrder->setBasketBindingApiKey($this->extractBasketBindingApiKeyByQuoteId((int)$order->getQuoteId()));
        $inPostPayOrder->setInPostPayAccountEmail($inPostOrder->getAccountInfo()->getMail());
        $inPostPayOrder->setOrderWithInvoice(false);

        if ($inPostOrder->getInvoiceDetails() !== null) {
            $inPostPayOrder->setOrderWithInvoice(true);
            $inPostPayOrder->setInPostPayInvoiceEmail($inPostOrder->getInvoiceDetails()->getMail());
        }

        if ($inPostOrder->getDelivery()->getDigitalDeliveryEmail()) {
            $inPostPayOrder->setDigitalDeliveryEmail($inPostOrder->getDelivery()->getDigitalDeliveryEmail());
        }

        if ($inPostOrder->getDelivery()->getMail()) {
            $inPostPayOrder->setDeliveryEmail($inPostOrder->getDelivery()->getMail());
        }

        $this->inPostPayOrderRepository->save($inPostPayOrder);

        $this->createLog(
            sprintf('InPost Pay Order entity has been initialized for order #%s', (string)$order->getIncrementId())
        );
    }

    /**
     * @param int $quoteId
     * @return string|null
     */
    private function extractBasketBindingApiKeyByQuoteId(int $quoteId): ?string
    {
        try {
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);

            return $inPostPayQuote->getBasketBindingApiKey();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
