<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Summary;

use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Notice extends DataObject implements NoticeInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        $type = $this->getData(self::TYPE);

        return is_scalar($type) ? (string)$type : '';
    }

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $description = $this->getData(self::DESCRIPTION);

        return is_scalar($description) ? (string)$description : '';
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }
}
