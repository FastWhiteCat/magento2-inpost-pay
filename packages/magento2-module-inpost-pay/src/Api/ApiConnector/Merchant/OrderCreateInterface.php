<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as OrderDataInterface;

/**
 * InPost Pay Order service that allows to create order from bound basket and passed parameters.
 * @api
 */
interface OrderCreateInterface
{
    public const INPOST_ORDER = 'inpost_order';

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface $orderDetails
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface $accountInfo
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface $delivery
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface[] $consents
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface|null $invoiceDetails
     * @return \InPost\InPostPay\Api\Data\Merchant\OrderInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     * @throws \InPost\InPostPay\Exception\OrderNotCreateException
     */
    public function execute(
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): OrderDataInterface;
}
