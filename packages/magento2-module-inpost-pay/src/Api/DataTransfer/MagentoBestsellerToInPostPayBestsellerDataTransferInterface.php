<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\DataTransfer;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface MagentoBestsellerToInPostPayBestsellerDataTransferInterface
{
    /**
     * @param InPostPayBestsellerProductInterface $magentoBestsellerProduct
     * @param BestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws NoSuchEntityException
     */
    public function transfer(
        InPostPayBestsellerProductInterface $magentoBestsellerProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void;
}
