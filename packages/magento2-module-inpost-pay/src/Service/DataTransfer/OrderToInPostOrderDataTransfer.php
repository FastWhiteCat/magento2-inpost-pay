<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderToInPostOrderDataTransfer
{
    /**
     * @var OrderToInPostOrderDataTransferInterface[]
     */
    private array $dataTransfers = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        array $dataTransfer = []
    ) {
        $this->initDataTransfers($dataTransfer);
    }

    /**
     * @param Order $order
     * @param InPostOrderInterface $inPostOrder
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function transfer(Order $order, InPostOrderInterface $inPostOrder): void
    {
        foreach ($this->dataTransfers as $dataTransfer) {
            $dataTransfer->transfer($order, $inPostOrder);
        }
    }

    /**
     * @param array $dataTransfers
     * @return void
     * @throws InvalidArgumentException
     */
    private function initDataTransfers(array $dataTransfers): void
    {
        foreach ($dataTransfers as $dataTransferKey => $dataTransfer) {
            if ($dataTransfer instanceof OrderToInPostOrderDataTransferInterface) {
                $this->dataTransfers[$dataTransferKey] = $dataTransfer;
            } else {
                $errorMsg = sprintf('Order to InPost Order data transfer: %s is not valid.', $dataTransferKey);
                $this->logger->critical($errorMsg);

                throw new InvalidArgumentException($errorMsg);
            }
        }
    }
}
