<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable;

interface DetailsInterface
{
    public const LINK = 'link';

    /**
     * @return string
     */
    public function getLink(): string;

    /**
     * @param string $link
     * @return void
     */
    public function setLink(string $link): void;
}
