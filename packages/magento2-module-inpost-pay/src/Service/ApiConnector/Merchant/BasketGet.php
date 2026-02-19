<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\ApiConnector\Merchant\BasketGetInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface as BasketDataInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Exception\BasketNotFoundException;
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
class BasketGet implements BasketGetInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly BasketInterfaceFactory $basketFactory,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $basketId
     * @return BasketDataInterface
     * @throws InPostPayBadRequestException
     * @throws InPostPayAuthorizationException
     * @throws BasketNotFoundException
     * @throws InPostPayInternalException
     */
    public function execute(string $basketId): BasketDataInterface
    {
        try {
            $this->eventManager->dispatch('izi_basket_get_before', [InPostPayQuoteInterface::BASKET_ID => $basketId]);

            $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
            $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
            $basket = $this->basketFactory->create();
            $basket->setBasketId($basketId);
            $this->quoteToBasketDataTransfer->transfer($quote, $basket);

            $this->eventManager->dispatch('izi_basket_get_after', [BasketConfirmationInterface::BASKET => $basket]);

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
}
