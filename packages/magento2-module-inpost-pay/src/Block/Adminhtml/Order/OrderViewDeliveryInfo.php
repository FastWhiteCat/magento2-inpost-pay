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
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Payment\Model\Method\Adapter as InPostPayAdapter;
use Psr\Log\LoggerInterface;

class OrderViewDeliveryInfo extends Template
{
    private const INPOST_DELIVERY_MODULE_NAME = 'Smartmage_Inpost';

    protected $_template = 'InPost_InPostPay::order/view/info.phtml';

    public function __construct(
        private readonly CurrentOrderRegistry $currentOrderRegistry,
        private readonly InPostPayAdapter $inPostPayAdapter,
        private readonly ModuleManager $moduleManager,
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
            $lockerId = $this->getCurrentOrder()->getData(
                InPostPayLockerIdProviderInterface::INPOST_PAY_LOCKER_ID_FIELD
            );
        } catch (LocalizedException $e) {
            $lockerId = null;
        }

        return (!empty($lockerId) && is_scalar($lockerId)) ? (string)$lockerId : null;
    }

    public function canShowLockerInfo(): bool
    {
        return $this->canShowInPostPayInfo() && !$this->isInPostDeliveryModuleEnabled();
    }

    public function canShowInPostPayInfo(): bool
    {
        try {
            $order = $this->getCurrentOrder();

            return !$order->getIsVirtual() && $this->isInPostPayOrder() && !$this->isInPostDeliveryModuleEnabled();
        } catch (LocalizedException $e) {
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
            return false;
        }

        if ($payment instanceof OrderPaymentInterface) {
            // @phpstan-ignore-next-line
            $orderPaymentCode = $payment->getMethodInstance()->getCode();
        }

        return isset($orderPaymentCode) && $orderPaymentCode === $this->inPostPayAdapter->getCode();
    }

    private function isInPostDeliveryModuleEnabled(): bool
    {
        return $this->moduleManager->isEnabled(self::INPOST_DELIVERY_MODULE_NAME);
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
