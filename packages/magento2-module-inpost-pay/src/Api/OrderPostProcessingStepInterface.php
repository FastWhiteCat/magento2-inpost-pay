<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

interface OrderPostProcessingStepInterface
{
    /**
     * @param Order $order
     * @param InPostOrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     */
    public function process(Order $order, InPostOrderInterface $inPostOrder): void;

    public function getStepCode(): string;
    public function setStepCode(string $stepCode): void;
}
