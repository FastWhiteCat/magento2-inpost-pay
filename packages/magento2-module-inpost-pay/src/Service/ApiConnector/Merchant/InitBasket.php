<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\InitBasketInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Exception\BasketNotFoundException;
use InPost\InPostPay\Exception\ProductNotAddedException;
use InPost\InPostPay\Exception\ProductNotFoundException;
use InPost\InPostPay\Service\Cart\CartService;
use InPost\InPostPay\Service\GetBasketId;
use Magento\Framework\Exception\CouldNotSaveException;
use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InitBasket implements InitBasketInterface
{
    private const DEFAULT_QTY = 1.00;

    public function __construct(
        private readonly CartService $cartService,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly GetBasketId $getBasketId,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly BasketInterfaceFactory $basketFactory,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $productId
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface $phoneNumber
     * @param string|null $basketId
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(
        string $productId,
        PhoneNumberInterface $phoneNumber,
        ?string $basketId = null
    ): BasketInterface {
        try {
            $this->eventManager->dispatch(
                'izi_init_basket_before',
                [
                    ProductInterface::PRODUCT_ID => $productId,
                    InPostPayQuoteInterface::PHONE_NUMBER => $phoneNumber,
                    InPostPayQuoteInterface::BASKET_ID => $basketId
                ]
            );

            try {
                if ($basketId) {
                    $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId($basketId);
                    $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
                } else {
                    $quote = $this->cartService->initCart();
                    $inPostPayQuote = $this->initInPostPayQuote($quote, $phoneNumber, $basketId);
                }
            } catch (CouldNotSaveException | NoSuchEntityException $e) {
                throw new BasketNotFoundException();
            }

            $this->addProductToCart($quote, $productId);
            $quote = $this->reloadQuote($quote);
            $basket = $this->prepareBasketObject($inPostPayQuote, $quote);

            $this->eventManager->dispatch(
                'izi_init_basket_after',
                [BasketConfirmationInterface::BASKET => $basket]
            );

            return $basket;
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
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param Quote $quote
     * @return BasketInterface
     */
    private function prepareBasketObject(InPostPayQuoteInterface $inPostPayQuote, Quote $quote): BasketInterface
    {
        $basket = $this->basketFactory->create();
        $basket->setBasketId($inPostPayQuote->getBasketId());
        $this->quoteToBasketDataTransfer->transfer($quote, $basket);

        return $basket;
    }

    /**
     * @param Quote $quote
     * @param string $productId
     * @return void
     * @throws ProductNotAddedException
     * @throws ProductNotFoundException
     */
    private function addProductToCart(Quote $quote, string $productId): void
    {
        try {
            $productIdParts = explode('_', $productId);
            $isQuoteItemId = false;

            if (isset($productIdParts[1])) {
                $productId = (int)$productIdParts[1];
                $isQuoteItemId = true;
            } else {
                $productId = (int)$productId;
            }

            $qty = self::DEFAULT_QTY;

            foreach ($quote->getAllVisibleItems() as $item) {
                $itemProductId = (int)$item->getProduct()->getId();

                if ($itemProductId === $productId) {
                    $qty += $item->getQty();

                    break;
                }
            }

            $this->cartService->addToCart($quote, $productId, $qty, $isQuoteItemId);
        } catch (NoSuchEntityException $e) {
            throw new ProductNotFoundException();
        } catch (LocalizedException $e) {
            throw new ProductNotAddedException(__($e->getMessage()));
        }
    }

    /**
     * @param int $quoteId
     * @return Quote
     * @throws NoSuchEntityException
     */
    private function getQuoteById(int $quoteId): Quote
    {
        try {
            $quote = $this->cartRepository->get($quoteId);

            if ($quote instanceof Quote) {
                return $quote;
            } else {
                throw new NoSuchEntityException(__('Quote with ID %1 is invalid.', $quoteId));
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param Quote $quote
     * @param PhoneNumberInterface $phoneNumber
     * @param string|null $basketId
     * @return InPostPayQuoteInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    private function initInPostPayQuote(
        Quote $quote,
        PhoneNumberInterface $phoneNumber,
        ?string $basketId = null
    ): InPostPayQuoteInterface {
        $tempBasketId = $this->getBasketId->get((int)$quote->getId(), true); //@phpstan-ignore-line
        $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId((string)$tempBasketId);

        if ($basketId) {
            $inPostPayQuote->setBasketId($basketId);
        } else {
            $basketId = $tempBasketId;
        }

        $inPostPayQuote->setStatus(InPostBasketStatus::SUCCESS->value);
        $inPostPayQuote->setMaskedPhoneNumber('');
        $inPostPayQuote->setPhone($phoneNumber->getPhone());
        $inPostPayQuote->setCountryPrefix($phoneNumber->getCountryPrefix());

        $inPostPayQuote->setName('');
        $inPostPayQuote->setSurname('');

        $this->inPostPayQuoteRepository->save($inPostPayQuote);

        return $this->inPostPayQuoteRepository->getByBasketId((string)$basketId);
    }

    /**
     * @param Quote $quote
     * @return Quote
     */
    private function reloadQuote(Quote $quote): Quote
    {
        try {
            return $this->cartRepository->get((int)$quote->getId());//@phpstan-ignore-line
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            return $quote;
        }
    }
}
