<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Refund\EventData;

use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\PaymentInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Payment extends DataObject implements PaymentInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getId(): string
    {
        $id = $this->getData(self::ID);

        return (is_scalar($id)) ? (string)$id : '';
    }

    /**
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->setData(self::ID, $id);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        $method = $this->getData(self::METHOD);

        return (is_scalar($method)) ? (string)$method : '';
    }

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->setData(self::METHOD, $method);
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        $reference = $this->getData(self::REFERENCE);

        return (is_scalar($reference)) ? (string)$reference : null;
    }

    /**
     * @param string|null $reference
     * @return void
     */
    public function setReference(?string $reference): void
    {
        $this->setData(self::REFERENCE, $reference);
    }
}
