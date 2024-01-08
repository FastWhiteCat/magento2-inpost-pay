<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Order;

use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use InPost\InPostPay\Model\Registry\CurrentOrderRegistry;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use InPost\InPostPay\Provider\InPostDeliveryModuleProvider;
use Magento\Payment\Model\Method\Adapter as InPostPayAdapter;
use Psr\Log\LoggerInterface;

class OrderViewDeliveryInfo extends Template
{
    protected $_template = 'InPost_InPostPay::order/view/info.phtml';

    public function __construct(
        private readonly CurrentOrderRegistry $currentOrderRegistry,
        private readonly InPostPayLockerIdProviderInterface $inPostPayLockerIdProvider,
        private readonly InPostPayAdapter $inPostPayAdapter,
        private readonly InPostDeliveryModuleProvider $inPostDeliveryModuleProvider,
        private readonly Escaper $escaper,
        private readonly LoggerInterface $logger,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getLockerId(): ?string
    {
        try {
            $orderId = $this->getCurrentOrder()->getId();
            if ($orderId && is_scalar($orderId)) {
                return $this->inPostPayLockerIdProvider->getFromOrderById((int) $orderId);
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    public function canShowLockerInfo(): bool
    {
        return $this->canShowInPostPayInfo() && !$this->inPostDeliveryModuleProvider->isEnabled();
    }

    public function canShowInPostPayInfo(): bool
    {
        try {
            return $this->isInPostPayOrder();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    public function getEscaper(): Escaper
    {
        return $this->escaper;
    }

    private function isInPostPayOrder(): bool
    {
        try {
            $payment = $this->getCurrentOrder()->getPayment();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        if ($payment instanceof OrderPaymentInterface) {
            // @phpstan-ignore-next-line
            $orderPaymentCode = $payment->getMethodInstance()->getCode();
        }

        return isset($orderPaymentCode) && $orderPaymentCode === $this->inPostPayAdapter->getCode();
    }

    /**
     * @return Order
     * @throws LocalizedException
     */
    private function getCurrentOrder(): Order
    {
        $order = $this->currentOrderRegistry->getOrder();
        if (!$order instanceof Order) {
            $errorPhrase = __('Cannot obtain order data.');
            $this->logger->error($errorPhrase->render());

            throw new LocalizedException($errorPhrase);
        }

        return $order;
    }
}
