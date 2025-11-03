<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config\OrderErrorsHandling;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Timeout
{
    public const TIMEOUT_THRESHOLD_SECONDS = 20.0;
    private const XML_PATH_IS_ENABLED = 'payment/inpost_pay/order_errors_handling_timeout_is_enabled';
    private const XML_PATH_CAN_CANCEL = 'payment/inpost_pay/order_errors_handling_timeout_can_cancel';
    private const XML_PATH_CAN_CHANGE_STATUS = 'payment/inpost_pay/order_errors_handling_timeout_can_change_status';
    private const XML_PATH_TIMED_OUT_STATUS = 'payment/inpost_pay/order_errors_handling_timed_out_order_status';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_ENABLED);
    }

    /**
     * @return bool
     */
    public function canCancel(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CAN_CANCEL);
    }

    /**
     * @return bool
     */
    public function canChangeStatus(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CAN_CHANGE_STATUS);
    }

    /**
     * @return string|null
     */
    public function getTimedOutStatus(): ?string
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_TIMED_OUT_STATUS);

        return is_scalar($value) && $value !== '' ? (string)$value : null;
    }
}
