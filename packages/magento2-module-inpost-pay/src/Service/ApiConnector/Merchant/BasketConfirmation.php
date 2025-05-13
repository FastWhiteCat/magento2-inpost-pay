<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Exception\BasketNotFoundException;
use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface;
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
class BasketConfirmation implements BasketConfirmationInterface
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
     * @param string|null $status
     * @param string|null $inpostBasketId
     * @param PhoneNumberInterface|null $phoneNumber
     * @param BrowserInterface|null $browser
     * @param string|null $maskedPhoneNumber
     * @param string|null $name
     * @param string|null $surname
     * @return BasketInterface
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(
        string $basketId,
        ?string $status = null,
        ?string $inpostBasketId = null,
        ?PhoneNumberInterface $phoneNumber = null,
        ?BrowserInterface $browser = null,
        ?string $maskedPhoneNumber = null,
        ?string $name = null,
        ?string $surname = null
    ): BasketInterface {
        try {
            $this->eventManager->dispatch(
                'izi_basket_confirmation_before',
                [
                    InPostPayQuoteInterface::BASKET_ID => $basketId,
                    InPostPayQuoteInterface::STATUS => $status,
                    InPostPayQuoteInterface::INPOST_BASKET_ID => $inpostBasketId,
                    InPostPayQuoteInterface::PHONE_NUMBER => $phoneNumber,
                    BasketConfirmationInterface::BROWSER => $browser,
                    InPostPayQuoteInterface::MASKED_PHONE_NUMBER => $maskedPhoneNumber,
                    sprintf('param_%s', InPostPayQuoteInterface::NAME) => $name,
                    InPostPayQuoteInterface::SURNAME => $surname
                ]
            );

            $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
            $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());

            $inPostPayQuote->setStatus($status ?? '');
            if ($status === InPostBasketStatus::REJECT->value) {
                return $this->rejectBasket($inPostPayQuote);
            }

            $inPostPayQuote->setInpostBasketId($inpostBasketId ?? '');
            $inPostPayQuote->setMaskedPhoneNumber($maskedPhoneNumber ?? '');
            if ($phoneNumber) {
                $inPostPayQuote->setPhone($phoneNumber->getPhone());
                $inPostPayQuote->setCountryPrefix($phoneNumber->getCountryPrefix());
            }
            $inPostPayQuote->setName($name ?? '');
            $inPostPayQuote->setSurname($surname ?? '');
            if ($browser) {
                $inPostPayQuote->setBrowserId($browser->getBrowserId());
                $inPostPayQuote->setBrowserTrusted($browser->getBrowserTrusted());
            }

            $this->inPostPayQuoteRepository->save($inPostPayQuote);

            $basket = $this->basketFactory->create();
            $basket->setBasketId($basketId);
            $this->quoteToBasketDataTransfer->transfer($quote, $basket);
            $this->eventManager->dispatch(
                'izi_basket_confirmation_after',
                [BasketConfirmationInterface::BASKET => $basket]
            );

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
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
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

    private function rejectBasket(InPostPayQuoteInterface $inPostPayQuote): BasketInterface
    {
        $basket = $this->basketFactory->create();
        $inPostPayQuoteId = is_scalar($inPostPayQuote->getInPostPayQuoteId())
            ? (int)$inPostPayQuote->getInPostPayQuoteId()
            : null;

        if ($inPostPayQuoteId) {
            $this->inPostPayQuoteRepository->deleteById($inPostPayQuoteId);
        }

        $basket->setStatus(InPostBasketStatus::REJECT->value);

        return $basket;
    }
}
