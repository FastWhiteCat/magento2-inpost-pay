<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response;

use Magento\Framework\DataObject;

class BasketResponse extends DataObject
{
    public const BASKET_ID = 'inpost_basket_id';

    public function getBasketId(): string
    {
        $basketId = $this->getData(self::BASKET_ID);

        return is_scalar($basketId) ? (string)$basketId : '';
    }

    public function setBasketId(string $basketId): void
    {
        $this->setData(self::BASKET_ID, $basketId);
    }
}
