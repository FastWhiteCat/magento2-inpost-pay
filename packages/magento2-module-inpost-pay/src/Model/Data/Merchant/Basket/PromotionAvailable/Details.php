<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\PromotionAvailable;

use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Details extends DataObject implements DetailsInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getLink(): string
    {
        $link = $this->getData(self::LINK);

        return is_scalar($link) ? (string)$link : '';
    }

    /**
     * @param string $link
     * @return void
     */
    public function setLink(string $link): void
    {
        $this->setData(self::LINK, $link);
    }
}
