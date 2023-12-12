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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Serialize\Serializer\Base64Json as Base64JsonSerializer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class BasketConfirmation implements BasketConfirmationInterface
{
    private const REQUEST_PREFIX = 'BASKET_CONFIRMATION_REQUEST';
    private const RESPONSE_PREFIX = 'BASKET_CONFIRMATION_RESPONSE';

    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly JsonSerializer $jsonSerializer,
        private readonly Base64JsonSerializer $base64JsonSerializer,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly BasketInterfaceFactory $basketFactory,
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
            $payload = $this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
            $payload = is_array($payload) ? $payload : [];

            $logMessage = sprintf('Confirmation for Basket ID: %s', $basketId);
            $this->createRequestDebugLog(self::REQUEST_PREFIX, $logMessage, $payload);

            $inPostPayQuote->setStatus($status);
            $inPostPayQuote->setInpostBasketId($inpostBasketId);
            $inPostPayQuote->setMaskedPhoneNumber($maskedPhoneNumber);
            $inPostPayQuote->setPhoneNumber(trim($phoneNumber->getCountryPrefix()) . trim($phoneNumber->getPhone()));
            $inPostPayQuote->setName($name);
            $inPostPayQuote->setSurname($surname);
            $inPostPayQuote->setBrowserId($browser->getBrowserId());
            $inPostPayQuote->setBrowserTrusted($browser->getBrowserTrusted());

            $this->inPostPayQuoteRepository->save($inPostPayQuote);

            $this->logger->info(sprintf('Basket ID %s has been confirmed.', $inPostPayQuote->getBasketId()));
        } catch (LocalizedException $e) {
            $errorMsg = __('Cannot confirm basket. Reason: %1', $e->getMessage());
            $this->logger->error($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
        $basket = $this->basketFactory->create();
        $this->quoteToBasketDataTransfer->transfer($quote, $basket);
        $this->createRequestDebugLog(self::RESPONSE_PREFIX, $logMessage, $basket->getData());

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

    private function createRequestDebugLog(string $logPrefix, string $message, array $data = []): void
    {
        $serializedData = ($data) ? $this->base64JsonSerializer->serialize($data) : '';
        $dataLabel = ($logPrefix === self::REQUEST_PREFIX) ? 'Payload' : 'Response';
        $this->logger->debug(sprintf('%s: %s %s: %s', $logPrefix, $message, $dataLabel, $serializedData));
    }
}
