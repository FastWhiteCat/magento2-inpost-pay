<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketUpdateInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\Cart\CartService;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasketUpdate implements BasketUpdateInterface
{
    private const REQUEST_PREFIX = 'BASKET_UPDATE_REQUEST';

    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartService $cartService,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly BasketInterfaceFactory $basketFactory,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $basketId
     * @param string $eventId
     * @param string $eventDataTime
     * @param string $eventType
     * @param QuantityUpdateInterface[]|null $quantityEventData
     * @param PromoCodeInterface[]|null $promoCodesEventData
     * @return BasketInterface
     * @throws LocalizedException
     */
    public function execute(
        string $basketId,
        string $eventId,
        string $eventDataTime,
        string $eventType,
        ?array $quantityEventData = null,
        ?array $promoCodesEventData = null,
    ): BasketInterface {
        $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
        $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());

        $this->eventManager->dispatch('izi_basket_update_before',
            [
                'quote' => $quote,
                'inPostPayQuote' => $inPostPayQuote,
                'basketId' => $basketId,
                'eventId' => $eventId,
                'eventDataTime' => $eventDataTime,
                'eventType' => $eventType,
                'quantityEventData' => $quantityEventData,
                'promoCodesEventData' => $promoCodesEventData
            ]);

        $this->createRequestDebugLog(
            sprintf(
                'Updating basket. Basket ID: %s, Event ID: %s Event Data Time: %s Event Type: %s',
                $basketId,
                $eventId,
                $eventDataTime,
                $eventType
            )
        );

        if (!empty($quantityEventData)) {
            foreach ($quantityEventData as $productQuantity) {
                $productId = (int)$productQuantity->getProductId();
                $qty = (float)$productQuantity->getQuantity()->getQuantity();
                $this->cartService->addToCart($quote, $productId, $qty);
            }
        }

        if ($promoCodesEventData) {
            foreach ($promoCodesEventData as $promoCode) {
                $this->cartService->applyPromo($quote, $promoCode->getPromoCodeValue());
            }
        }

        $reloadedQuote = $this->reloadQuote((int)(is_scalar($quote->getId()) ? (int)$quote->getId() : null));
        $basket = $this->basketFactory->create();
        $this->quoteToBasketDataTransfer->transfer($reloadedQuote ?? $quote, $basket);
        $this->createRequestDebugLog(sprintf('Basket ID: %s has been updated.', $basketId));

        $this->eventManager->dispatch('izi_basket_update_after', ['basket' => $basket]);

        return $basket;
    }

    /**
     * @param string $basketId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    private function getInPostPayQuoteByBasketId(string $basketId): InPostPayQuoteInterface
    {
        try {
            return $this->inPostPayQuoteRepository->getByBasketId($basketId);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param int $quoteId
     * @return Quote
     * @throws LocalizedException
     */
    private function getQuoteById(int $quoteId): Quote
    {
        try {
            $quote = $this->cartRepository->get($quoteId);

            if ($quote instanceof Quote) {
                return $quote;
            } else {
                throw new LocalizedException(__('Quote with ID %1 is invalid.', $quoteId));
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    private function reloadQuote(int $quoteId): ?Quote
    {
        try {
            $quote = $this->cartRepository->get($quoteId);
        } catch (LocalizedException $e) {
            $this->logger->error(sprintf('Reloading quote failed. Reason: %s', $e->getMessage()));
        }

        return (isset($quote) && $quote instanceof Quote) ? $quote : null;
    }

    private function createRequestDebugLog(string $message): void
    {
        $this->logger->debug(sprintf('%s: %s', self::REQUEST_PREFIX, $message));
    }
}
