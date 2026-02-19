<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Registry\InPostPayMobileAppOrderRegistry;
use Magento\Framework\Exception\NoSuchEntityException;

class RegisterMobileAppOrderPlugin
{
    private readonly InPostPayMobileAppOrderRegistry $mobileAppOrderRegistry;

    private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository;

    /**
     * @param InPostPayMobileAppOrderRegistry $mobileAppOrderRegistry
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     */
    public function __construct(
        InPostPayMobileAppOrderRegistry $mobileAppOrderRegistry,
        InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
    ) {
        $this->mobileAppOrderRegistry = $mobileAppOrderRegistry;
        $this->inPostPayQuoteRepository = $inPostPayQuoteRepository;
    }

    /**
     * Before execute plugin to register mobile app orders
     *
     * @param OrderCreateInterface $subject
     * @param OrderDetailsInterface $orderDetails
     * @param AccountInfoInterface $accountInfo
     * @param DeliveryInterface $delivery
     * @param array $consents
     * @param InvoiceDetailsInterface|null $invoiceDetails
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        OrderCreateInterface $subject,
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): array {
        try {
            $basketId = $orderDetails->getBasketId();
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId($basketId);
            $this->mobileAppOrderRegistry->registerMobileAppQuote((int)$inPostPayQuote->getQuoteId());
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
        }

        return [$orderDetails, $accountInfo, $delivery, $consents, $invoiceDetails];
    }
}
