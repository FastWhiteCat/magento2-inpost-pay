<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderEvent;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\TransactionFactory as DbTransactionFactory;
use Magento\Framework\DB\Transaction as DbTransaction;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\OrderRepositoryInterface;
use Throwable;

class UpdateFreeOrderPayment extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    /**
     * @param InvoiceService $invoiceService
     * @param DbTransactionFactory $dbTransactionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly DbTransactionFactory $dbTransactionFactory,
        private readonly OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @param Order $order
     * @param InPostOrderInterface $inPostOrder
     * @return void
     */
    public function process(Order $order, InPostOrderInterface $inPostOrder): void
    {
        $paymentType = $inPostOrder->getOrderDetails()->getPaymentType();

        if ($paymentType === OrderDetailsInterface::FREE_ORDER
            && (float)$order->getGrandTotal() === 0.00
            && $order->canInvoice()
        ) {
            try {
                /** @var DbTransaction $dbTransaction */
                $dbTransaction = $this->dbTransactionFactory->create();
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG, true);
                $transactionSave = $dbTransaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();

                $comment = __(
                    'InPost Pay Free Order #%1 has been automatically marked as paid.',
                    $order->getIncrementId()
                );
                $order->setState(Order::STATE_PROCESSING);
                $order->addCommentToStatusHistory(
                    $comment->render(),
                    $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING) ?? false,
                    false
                )->setIsCustomerNotified(0);
                $order->setData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG, true);
                $this->orderRepository->save($order);
                $this->createLog($comment->render());
            } catch (Throwable $e) {
                $this->createLog(
                    sprintf(
                        'Could not automatically mark InPost Pay Free Order #%s as paid. Reason: %s',
                        $order->getIncrementId(),
                        $e->getMessage()
                    ),
                    [],
                    true
                );
            }
        }
    }
}
