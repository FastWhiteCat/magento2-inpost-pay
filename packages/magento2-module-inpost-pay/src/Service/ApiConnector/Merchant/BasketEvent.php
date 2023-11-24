<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketEventInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\Validator\SignatureValidatorInterface;
use InPost\InPostPay\Service\Cart\CartService;
use InPost\InPostPay\Service\Converter\QuoteToBasketDataConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class BasketEvent extends MerchantEndpoint implements BasketEventInterface
{
    private const EVENT_TYPE = 'event_type';
    private const INCREMENTING_PRODUCT_QUANTITY_EVENT = 'PRODUCTS_QUANTITY';
    private const ADDING_RELATED_PRODUCTS_EVENT = 'RELATED_PRODUCTS';
    private const APPLYING_PROMO_CODE_EVENT = 'PROMO_CODES';
    private const CART_PRODUCTS_EVENT_DATA = 'quantity_event_data';
    private const RELATED_PRODUCTS_EVENT_DATA = 'related_products_event_data';
    private const PROMO_CODES_EVENT_DATA = 'promo_codes_event_data';
    private const PROMO_CODE_VALUE = 'promo_code_value';
    private const PRODUCT_ID = 'product_id';
    private const QUANTITY = 'quantity';

    private array $addToCartEvents = [
        self::INCREMENTING_PRODUCT_QUANTITY_EVENT,
        self::ADDING_RELATED_PRODUCTS_EVENT
    ];

    private array $promoEvents = [
        self::APPLYING_PROMO_CODE_EVENT
    ];

    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly SignatureValidatorInterface $signatureValidator,
        private readonly LoggerInterface $logger,
        private readonly JsonSerializer $jsonSerializer,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartService $cartService,
        private readonly QuoteToBasketDataConverter $quoteToBasketDataConverter
    ) {
        parent::__construct($restRequest, $signatureValidator, $logger);
    }

    /**
     * @param string $basketId
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $basketId): array
    {
        $this->validateRequest();
        $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
        $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
        $requestParams = $this->jsonSerializer->unserialize((string)$this->restRequest->getContent());

        $result = false;
        if ($this->isProductAddEvent($requestParams)) {
            $result = $this->processAddToCart($quote, $requestParams);
        }

        if ($this->isPromoApplyEvent($requestParams)) {
            $result = $this->processApplyPromo($quote, $requestParams);
        }

        if (!$result) {
            $this->logger->info('Quote has not been changed after processing this payload.', $requestParams);
        }

        $reloadedQuote = $this->reloadQuote((int)$quote->getId());

        return $this->quoteToBasketDataConverter->convert($reloadedQuote ?? $quote);
    }

    private function isProductAddEvent(array $requestParams): bool
    {
        if (isset($requestParams[self::EVENT_TYPE])) {
            $eventType = $requestParams[self::EVENT_TYPE];
        }

        return isset($eventType) && in_array($eventType, $this->addToCartEvents);
    }

    private function isPromoApplyEvent(array $requestParams): bool
    {
        if (isset($requestParams[self::EVENT_TYPE])) {
            $eventType = $requestParams[self::EVENT_TYPE];
        }

        return isset($eventType) && in_array($eventType, $this->promoEvents);
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
        } catch (NoSuchEntityException $e) {
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
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param Quote $quote
     * @param array $requestParams
     * @return bool
     * @throws LocalizedException
     */
    private function processAddToCart(Quote $quote, array $requestParams): bool
    {
        $productsData = [];
        if (isset($requestParams[self::CART_PRODUCTS_EVENT_DATA])
            && is_array($requestParams[self::CART_PRODUCTS_EVENT_DATA])
        ) {
            $productsData = array_merge($productsData, $requestParams[self::CART_PRODUCTS_EVENT_DATA]);
        }

        if (isset($requestParams[self::RELATED_PRODUCTS_EVENT_DATA])
            && is_array($requestParams[self::RELATED_PRODUCTS_EVENT_DATA])
        ) {
            $productsData = array_merge($productsData, $requestParams[self::RELATED_PRODUCTS_EVENT_DATA]);
        }

        if (empty($productsData)) {
            return false;
        }

        foreach ($productsData as $productData) {
            $qty = $this->collectQtyFromProductData($productData);
            if (isset($productData[self::PRODUCT_ID]) && is_scalar($productData[self::PRODUCT_ID])) {
                $productId = (int)$productData[self::PRODUCT_ID];
                $this->cartService->addToCart($quote, $productId, $qty);
            }
        }

        return true;
    }

    /**
     * @param Quote $quote
     * @param array $requestParams
     * @return bool
     * @throws LocalizedException
     */
    private function processApplyPromo(Quote $quote, array $requestParams): bool
    {
        if (isset($requestParams[self::PROMO_CODES_EVENT_DATA])
            && is_array($requestParams[self::PROMO_CODES_EVENT_DATA])
        ) {
            foreach ($requestParams[self::PROMO_CODES_EVENT_DATA] as $promoCodeData) {
                if (isset($promoCodeData[self::PROMO_CODE_VALUE])
                    && is_scalar($promoCodeData[self::PROMO_CODE_VALUE])
                ) {
                    $this->cartService->applyPromo($quote, (string)$promoCodeData[self::PROMO_CODE_VALUE]);

                    return true;
                }
            }
        }

        return false;
    }

    private function collectQtyFromProductData(array $productData): float
    {
        $totalQuantity = 0;
        if (isset($productData[self::QUANTITY])
            && is_array($productData[self::QUANTITY])) {
            foreach ($productData[self::QUANTITY] as $qty) {
                $qty = (float)(is_scalar($qty) ? (float)$qty : 0);
                $totalQuantity += $qty;
            }
        }

        return (float)$totalQuantity;
    }

    private function reloadQuote(int $quoteId): ?Quote
    {
        try {
            $quote = $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(__('Reloading quote failed. Reason: %1', $e->getMessage()));
        }

        return (isset($quote) && $quote instanceof Quote) ? $quote : null;
    }
}
