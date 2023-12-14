<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface as BasketDataInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
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
    private const REQUEST_PREFIX = 'BASKET_CONFIRMATION_REQUEST';

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
     * @param string $status
     * @param string $inpostBasketId
     * @param PhoneNumberInterface $phoneNumber
     * @param BrowserInterface $browser
     * @param string $maskedPhoneNumber
     * @param string $name
     * @param string $surname
     * @return BasketDataInterface
     * @throws LocalizedException
     */
    public function execute(
        string $basketId,
        string $status,
        string $inpostBasketId,
        PhoneNumberInterface $phoneNumber,
        BrowserInterface $browser,
        string $maskedPhoneNumber,
        string $name,
        string $surname
    ): BasketDataInterface {
        try {
            $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
            $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());

            $this->eventManager->dispatch('izi_basket_confirmation_before',
                [
                    'quote' => $quote,
                    'inPostPayQuote' => $inPostPayQuote,
                    'basketId' => $basketId,
                    'status' => $status,
                    'inpostBasketId' => $inpostBasketId,
                    'phoneNumber' => $phoneNumber,
                    'browser' => $browser,
                    'maskedPhoneNumber' => $maskedPhoneNumber,
                    'name' => $name,
                    'surname' => $surname
                ]);
            $this->createRequestDebugLog(sprintf('Confirmation for Basket ID: %s Status: %s', $basketId, $status));

            $inPostPayQuote->setStatus($status);
            $inPostPayQuote->setInpostBasketId($inpostBasketId);
            $inPostPayQuote->setMaskedPhoneNumber($maskedPhoneNumber);
            $inPostPayQuote->setPhone($phoneNumber->getPhone());
            $inPostPayQuote->setCountryPrefix($phoneNumber->getCountryPrefix());
            $inPostPayQuote->setName($name);
            $inPostPayQuote->setSurname($surname);
            $inPostPayQuote->setBrowserId($browser->getBrowserId());
            $inPostPayQuote->setBrowserTrusted($browser->getBrowserTrusted());

            $this->inPostPayQuoteRepository->save($inPostPayQuote);
        } catch (LocalizedException $e) {
            $errorMsg = __('Cannot confirm basket. Reason: %1', $e->getMessage());
            $this->logger->error($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
        $basket = $this->basketFactory->create();
        $this->quoteToBasketDataTransfer->transfer($quote, $basket);

        $this->eventManager->dispatch('izi_basket_confirmation_before',
            [
                'basketId' => $basketId,
                'status' => $status,
                'inpostBasketId' => $inpostBasketId,
                'phoneNumber' => $phoneNumber,
                'browser' => $browser,
                'maskedPhoneNumber' => $maskedPhoneNumber,
                'name' => $name,
                'surname' => $surname
            ]);

        $this->createRequestDebugLog(
            sprintf(
                'Basket ID %s has been confirmed with status: %s',
                $inPostPayQuote->getBasketId(),
                $status
            )
        );

        $this->eventManager->dispatch('izi_basket_confirmation_after', ['basket' => $basket]);

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

    private function createRequestDebugLog(string $message): void
    {
        $this->logger->debug(sprintf('%s: %s', self::REQUEST_PREFIX, $message));
    }
}
