<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayAvailablePaymentMethodInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class InPostPayAvailablePaymentMethod extends AbstractModel implements InPostPayAvailablePaymentMethodInterface
{
    protected $_eventPrefix = InPostPayAvailablePaymentMethodInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayAvailablePaymentMethodInterface::ENTITY_NAME;

    public function _construct(): void
    {
        $this->_init(ResourceModel\InPostPayAvailablePaymentMethod::class);
    }

    public function getPaymentMethodId(): ?int
    {
        $id = ($this->hasData(self::PAYMENT_METHOD_ID)) ? $this->getData(self::PAYMENT_METHOD_ID) : null;

        return ($id && is_scalar($id)) ? (int)$id : null;
    }

    public function setPaymentMethodId(int $paymentMethodId): InPostPayAvailablePaymentMethodInterface
    {
        return $this->setData(self::PAYMENT_METHOD_ID, $paymentMethodId);
    }

    public function getPaymentCode(): string
    {
        $paymentCode = ($this->hasData(self::PAYMENT_CODE)) ? $this->getData(self::PAYMENT_CODE) : null;

        if ($paymentCode && is_scalar($paymentCode)) {
            return (string)$paymentCode;
        }

        throw new LocalizedException(__('Invalid Payment Code value.'));
    }

    public function setPaymentCode(string $paymentCode): InPostPayAvailablePaymentMethodInterface
    {
        return $this->setData(self::PAYMENT_CODE, $paymentCode);
    }
}
