<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\BasketUpdateInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Exception\BasketNotFoundException;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote;
use InPost\InPostPay\Service\Cart\CartService;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasketUpdate implements BasketUpdateInterface
{
    private const REQUEST_PREFIX = 'BASKET_UPDATE_REQUEST';
    private const PROMO_CODES_EVENT = 'PROMO_CODES';

    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartService $cartService,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly BasketInterfaceFactory $basketFactory,
        private readonly InPostPayQuote $inPostPayQuote,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $basketId
     * @param string $eventId
     * @param string $eventDataTime
     * @param string $eventType
     * @param QuantityUpdateInterface[]|null $quantityEventData
     * @param QuantityUpdateInterface[]|null $relatedProductsEventData
     * @param PromoCodeInterface[]|null $promoCodesEventData
     * @return BasketInterface
     * @throws InPostPayBadRequestException
     * @throws InPostPayAuthorizationException
     * @throws BasketNotFoundException
     * @throws InPostPayInternalException
     */
    public function execute(
        string $basketId,
        string $eventId,
        string $eventDataTime,
        string $eventType,
        ?array $quantityEventData = null,
        ?array $relatedProductsEventData = null,
        ?array $promoCodesEventData = null,
    ): BasketInterface {
        try {
            $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
            $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
            $this->createRequestDebugLog(
                sprintf(
                    'Updating basket. Basket ID: %s, Event ID: %s Event Data Time: %s Event Type: %s',
                    $basketId,
                    $eventId,
                    $eventDataTime,
                    $eventType
                )
            );

            $this->updateQuote(
                $quote,
                $eventType,
                $quantityEventData,
                $relatedProductsEventData,
                $promoCodesEventData
            );

            $reloadedQuote = $this->reloadQuote((int)(is_scalar($quote->getId()) ? (int)$quote->getId() : null));
            $basket = $this->basketFactory->create();
            $this->quoteToBasketDataTransfer->transfer($reloadedQuote ?? $quote, $basket);
            $this->inPostPayQuote->updateRefreshRequired($inPostPayQuote->getBasketId(), true);
            $this->createRequestDebugLog(sprintf('Basket ID: %s has been updated.', $basketId));

        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw new BasketNotFoundException();
        } catch (InPostPayAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new InPostPayBadRequestException();
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            throw new InPostPayInternalException();
        }
        return $basket;
    }

    /**
     * @param Quote $quote
     * @param string $eventType
     * @param QuantityUpdateInterface[]|null $quantityEventData
     * @param QuantityUpdateInterface[]|null $relatedProductsEventData
     * @param PromoCodeInterface[]|null $promoCodesEventData
     * @return void
     * @throws LocalizedException
     */
    private function updateQuote(
        Quote $quote,
        string $eventType,
        ?array $quantityEventData = null,
        ?array $relatedProductsEventData = null,
        ?array $promoCodesEventData = null,
    ): void {
        if (!empty($quantityEventData)) {
            foreach ($quantityEventData as $productQuantity) {
                $this->handleProductQuantities($quote, $productQuantity);
            }
        }

        if (!empty($relatedProductsEventData)) {
            foreach ($relatedProductsEventData as $productQuantity) {
                $this->handleProductQuantities($quote, $productQuantity);
            }
        }

        if ($promoCodesEventData) {
            foreach ($promoCodesEventData as $promoCode) {
                $this->cartService->applyPromo($quote, $promoCode->getPromoCodeValue());
            }
        } elseif ($eventType === self::PROMO_CODES_EVENT) {
            $this->cartService->removePromosFromQuote($quote);
        }
    }

    private function handleProductQuantities(Quote $quote, QuantityUpdateInterface $productQuantity): void
    {
        $productId = (int)$productQuantity->getProductId();
        $qty = (float)$productQuantity->getQuantity()->getQuantity();
        if ($qty) {
            $this->cartService->addToCart($quote, $productId, $qty);
        } else {
            $this->cartService->removeFromCart($quote, $productId);
        }
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
