<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Exception\InvalidPromoCodeException;
use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\ShippingMethod\ShippingMethodEstimator;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Service\PrepareQuoteProductsQuantity;
use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
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
use InPost\InPostPay\Validator\QuoteItemQtyValidator;
use Magento\Framework\Event\ManagerInterface as EventManager;
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
    private const PROMO_CODES_EVENT = 'PROMO_CODES';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartService $cartService,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly BasketInterfaceFactory $basketFactory,
        private readonly InPostPayQuote $inPostPayQuote,
        private readonly EventManager $eventManager,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly PrepareQuoteProductsQuantity $prepareQuoteProductsQuantity,
        private readonly QuoteItemQtyValidator $qtyValidator,
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
            $this->eventManager->dispatch('izi_basket_update_before', [
                InPostPayQuoteInterface::BASKET_ID => $basketId,
                BasketUpdateInterface::EVENT_ID => $eventId,
                BasketUpdateInterface::EVENT_DATA_TIME => $eventDataTime,
                BasketUpdateInterface::EVENT_TYPE => $eventType,
                BasketUpdateInterface::QUANTITY_EVENT_DATA => $quantityEventData,
                BasketUpdateInterface::RELATED_PRODUCTS_EVENT_DATA => $relatedProductsEventData,
                BasketUpdateInterface::PROMO_CODES_EVENT_DATA => $promoCodesEventData
            ]);

            $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
            $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $quote->setData(InPostPayQuoteInterface::INPOST_BASKET_ID, $basketId);

            $shippingAddress = $quote->getShippingAddress();

            if (empty($shippingAddress->getCountryId())) {
                $shippingAddress->setCountryId(ShippingMethodEstimator::DEFAULT_COUNTRY_ID);
            }

            try {
                $this->updateQuote(
                    $quote,
                    $eventType,
                    $quantityEventData,
                    $relatedProductsEventData,
                    $promoCodesEventData
                );
            } catch (InvalidPromoCodeException|LocalizedException $e) {
                $this->createBasketNotice->execute(
                    $basketId,
                    InPostPayBasketNoticeInterface::ERROR,
                    $e->getMessage()
                );
            }

            $reloadedQuote = $this->reloadQuote((int)(is_scalar($quote->getId()) ? (int)$quote->getId() : null));
            $basket = $this->basketFactory->create();
            $basket->setBasketId($basketId);
            $this->quoteToBasketDataTransfer->transfer($reloadedQuote ?? $quote, $basket);
            $this->inPostPayQuote->updateCartVersion($inPostPayQuote->getBasketId());

            $this->eventManager->dispatch('izi_basket_update_after', [BasketConfirmationInterface::BASKET => $basket]);

            return $basket;
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
            $promoCode = end($promoCodesEventData);
            $this->cartService->applyPromo($quote, $promoCode->getPromoCodeValue());
        } elseif ($eventType === self::PROMO_CODES_EVENT) {
            $this->cartService->removePromosFromQuote($quote);
        }
    }

    private function handleProductQuantities(Quote $quote, QuantityUpdateInterface $productQuantity): void
    {
        $productIdArr = explode('_', $productQuantity->getProductId());
        $isQuoteItemId = false;
        if (isset($productIdArr[1])) {
            $productId = (int)$productIdArr[1];
            $isQuoteItemId = true;
        } else {
            $productId = (int)$productQuantity->getProductId();
        }

        $qty = (float)$productQuantity->getQuantity()->getQuantity();
        if ($qty) {
            $quoteItemsQuantity = $this->prepareQuoteProductsQuantity->execute($quote);
            $this->qtyValidator->validate($quote, $productId, $qty, $isQuoteItemId, $quoteItemsQuantity);
            $this->cartService->addToCart($quote, $productId, $qty, $isQuoteItemId);
        } else {
            $this->cartService->removeFromCart($quote, $productId, $isQuoteItemId);
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
}
