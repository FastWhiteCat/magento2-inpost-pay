<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Model\Config\Payment\TitleMapper;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Payment\Model\Method\Substitution;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class UpdatePaymentTitleStep extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    public function __construct(
        protected readonly TitleMapper $titleMapper,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Order $order, InPostOrderInterface $inPostOrder): void
    {
        $paymentType = $inPostOrder->getOrderDetails()->getPaymentType();
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if (isset($additionalInformation[Substitution::INFO_KEY_TITLE])) {
            $oldTitle = $additionalInformation[Substitution::INFO_KEY_TITLE];
            $additionalInformation[Substitution::INFO_KEY_TITLE] = $this->getMappedTitle(
                $additionalInformation[Substitution::INFO_KEY_TITLE],
                $paymentType
            );
            $order->getPayment()->setAdditionalInformation(
                $additionalInformation
            );
            $this->createLog(
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
