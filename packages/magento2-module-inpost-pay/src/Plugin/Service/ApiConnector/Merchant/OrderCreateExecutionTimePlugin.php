<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Provider\Config\OrderErrorsHandling\Timeout;
use Psr\Log\LoggerInterface;
use Throwable;

class OrderCreateExecutionTimePlugin
{
    public const TIMEOUT_THRESHOLD_SECONDS = 20.0;

    /**
     * Keeps start times per subject instance.
     * @var array<string,float>
     */
    private array $startTimes = [];

    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param OrderCreateInterface $subject
     * @param mixed ...$args
     * @return array|null
     */
    public function beforeExecute(OrderCreateInterface $subject, ...$args): ?array
    {
        $key = spl_object_hash($subject);
        $this->startTimes[$key] = microtime(true);

        return null;
    }

    /**
     * @param OrderCreateInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     */
    public function afterExecute(OrderCreateInterface $subject, OrderInterface $result): OrderInterface
    {
        $key = spl_object_hash($subject);
        $start = $this->startTimes[$key] ?? null;

        if ($start !== null) {
            $duration = microtime(true) - $start;
            unset($this->startTimes[$key]);

            $this->logger->info(sprintf('OrderCreateInterface::execute took %.2f seconds', $duration));

            if ($duration > Timeout::TIMEOUT_THRESHOLD_SECONDS) {
                try {
                    $orderDetails = $result->getOrderDetails();
                    $orderIdStr = $orderDetails->getOrderId();
                    $orderId = is_numeric($orderIdStr) ? (int)$orderIdStr : 0;

                    if ($orderId > 0) {
                        $entity = $this->inPostPayOrderRepository->getByOrderId($orderId, true);
                        $entity->setIsTimedOut(true);
                        $this->inPostPayOrderRepository->save($entity);
                        $this->logger->warning(
                            sprintf(
                                'Marked InPost Pay order as timed out (order_id=%d, duration=%.2fs)',
                                $orderId,
                                $duration
                            )
                        );
                    } else {
                        $this->logger->warning('Could not determine Magento order ID to mark timeout.');
                    }
                } catch (Throwable $e) {
                    $this->logger->error('Failed to mark InPost Pay order as timed out: ' . $e->getMessage());
                }
            }
        } else {
            $this->logger->notice('OrderCreateInterface::execute after plugin executed without recorded start time.');
        }

        return $result;
    }
}
