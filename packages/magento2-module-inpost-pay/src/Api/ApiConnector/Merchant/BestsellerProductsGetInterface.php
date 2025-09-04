<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductsGetRequestInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;

/**
 * InPost Pay Bestseller Products service that allows for getting list of configured as Bestsellers products.
 * @api
 */
interface BestsellerProductsGetInterface
{
    public const RESPONSE = 'bestseller_products_get_response';
    public const PAGE_INDEX_PARAM = 'page_index';
    public const PAGE_SIZE_PARAM = 'page_size';
    public const PRODUCT_ID_PARAM = 'product_id';

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\BestsellersInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BestsellerProductNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(): BestsellersInterface;
}
