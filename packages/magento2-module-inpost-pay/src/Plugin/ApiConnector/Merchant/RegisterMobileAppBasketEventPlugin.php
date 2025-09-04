<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketUpdateInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Registry\InPostPayMobileAppOrderRegistry;
use Magento\Framework\Exception\NoSuchEntityException;

class RegisterMobileAppBasketEventPlugin
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
     * @param BasketUpdateInterface $subject
     * @param string $basketId
     * @param string $eventId
     * @param string $eventDataTime
     * @param string $eventType
     * @param QuantityUpdateInterface[]|null $quantityEventData
     * @param QuantityUpdateInterface[]|null $relatedProductsEventData
     * @param PromoCodeInterface[]|null $promoCodesEventData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        BasketUpdateInterface $subject,
        string $basketId,
        string $eventId,
        string $eventDataTime,
        string $eventType,
        ?array $quantityEventData = null,
        ?array $relatedProductsEventData = null,
        ?array $promoCodesEventData = null,
    ): array {
        try {
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId($basketId);
            $this->mobileAppOrderRegistry->registerMobileAppQuote((int)$inPostPayQuote->getQuoteId());
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
        }

        return [
            $basketId,
            $eventId,
            $eventDataTime,
            $eventType,
            $quantityEventData,
            $relatedProductsEventData,
            $promoCodesEventData
        ];
    }
}
