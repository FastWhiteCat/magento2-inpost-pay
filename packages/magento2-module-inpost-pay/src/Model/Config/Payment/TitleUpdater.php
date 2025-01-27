<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Payment;

use Magento\Payment\Model\Method\Substitution;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Repository as OrderPaymentRepository;
use Psr\Log\LoggerInterface;

class TitleUpdater
{
    public function __construct(
        private readonly TitleMapper $titleMapper,
        private readonly OrderPaymentRepository $orderPaymentRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function updatePaymentTitleByType(
        OrderPaymentInterface $payment,
        Order $order,
        string $paymentType
    ): void {
        $additionalInformation = $payment->getAdditionalInformation();
        if (isset($additionalInformation[Substitution::INFO_KEY_TITLE])) {
            $oldTitle = $additionalInformation[Substitution::INFO_KEY_TITLE];
            $newTitle = $this->getMappedTitle(
                $additionalInformation[Substitution::INFO_KEY_TITLE],
                $paymentType
            );
            if ($oldTitle === $newTitle || !$newTitle) {
                return;
            }
            $additionalInformation[Substitution::INFO_KEY_TITLE] = $newTitle;
            $payment->setAdditionalInformation(
                $additionalInformation
            );
            $this->orderPaymentRepository->save($payment);
            $this->logger->info(
                sprintf(
                    'Payment title %s has been updated to %s for Order #%s',
                    $oldTitle,
                    $additionalInformation[Substitution::INFO_KEY_TITLE],
                    (string)$order->getIncrementId()
                )
            );
        }
    }

    private function getMappedTitle(string $title, string $code): string
    {
        $mappedTitle = $this->titleMapper->getTitle($code);
        return $mappedTitle ?? $title;
    }
}
