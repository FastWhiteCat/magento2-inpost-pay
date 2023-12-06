<?php

namespace InPost\InPostPay\Api\Widget\Basket;

/**
 * @api
 */
interface MobileLinkInterface
{
    public const LINK = 'link';

    /**
     * @return string|null
     */
    public function getLink(): ?string;
}
