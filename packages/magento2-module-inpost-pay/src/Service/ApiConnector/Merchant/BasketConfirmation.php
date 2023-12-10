<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
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
    private const BASKET_BIND_STATUS_PARAM = 'status';
    private const INPOST_BASKET_ID_PARAM = 'inpost_basket_id';
    private const PHONE_NUMBER_PARAM = 'phone_number';
    private const PHONE_PARAM = 'phone';
    private const COUNTRY_PREFIX_PARAM = 'country_prefix';
    private const BROWSER_PARAM = 'browser';
    private const BROWSER_TRUSTED_PARAM = 'browser_trusted';
    private const BROWSER_ID_PARAM = 'browser_id';
    private const MASKED_PHONE_NUMBER_PARAM = 'masked_phone_number';
    private const NAME_PARAM = 'name';
    private const SURNAME_PARAM = 'surname';

    private const REQUEST_PREFIX = 'BASKET_CONFIRMATION_REQUEST';
    private const RESPONSE_PREFIX = 'BASKET_CONFIRMATION_RESPONSE';

    public function __construct(
        private readonly RestRequest                       $restRequest,
        private readonly JsonSerializer                    $jsonSerializer,
        private readonly Base64JsonSerializer              $base64JsonSerializer,
        private readonly CartRepositoryInterface           $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly QuoteToBasketDataTransfer         $quoteToBasketDataTransfer,
        private readonly LoggerInterface                   $logger
    ) {
    }

    /**
     * @param string $basketId
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $basketId): array
    {
        try {
            $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
            $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
            $payload = $this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
            $payload = is_array($payload) ? $payload : [];

            $logMessage = sprintf('Confirmation for Basket ID: %s', $basketId);
            $this->createRequestDebugLog(self::REQUEST_PREFIX, $logMessage, $payload);

            $inPostPayQuote->setStatus($this->extractStatus($payload));
            $inPostPayQuote->setInpostBasketId($this->extractInPostBasketId($payload));
            $inPostPayQuote->setMaskedPhoneNumber($this->extractMaskedPhoneNumber($payload));
            $inPostPayQuote->setPhoneNumber($this->extractPhoneNumber($payload));
            $inPostPayQuote->setName($this->extractName($payload));
            $inPostPayQuote->setSurname($this->extractSurname($payload));
            $inPostPayQuote->setBrowserId($this->extractBrowserId($payload));
            $inPostPayQuote->setBrowserTrusted($this->extractBrowserTrusted($payload));

            $this->inPostPayQuoteRepository->save($inPostPayQuote);

            $this->logger->info(sprintf('Basket ID %s has been confirmed.', $inPostPayQuote->getBasketId()));
        } catch (LocalizedException $e) {
            $errorMsg = __('Cannot confirm basket. Reason: %1', $e->getMessage());
            $this->logger->error($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }

        $basketData = $this->quoteToBasketDataTransfer->transfer($quote);
        $this->createRequestDebugLog(self::RESPONSE_PREFIX, $logMessage, $basketData);

        return $basketData;
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
     * @param array $payload
     * @return string
     */
    private function extractStatus(array $payload): string
    {
        $status = '';
        if (isset($payload[self::BASKET_BIND_STATUS_PARAM])
            && is_scalar($payload[self::BASKET_BIND_STATUS_PARAM])
        ) {
            $status = (string)$payload[self::BASKET_BIND_STATUS_PARAM];
        }

        return $status;
    }

    /**
     * @param array $payload
     * @return string
     */
    private function extractInPostBasketId(array $payload): string
    {
        $inPostBasketId = '';
        if (isset($payload[self::INPOST_BASKET_ID_PARAM]) && is_scalar($payload[self::INPOST_BASKET_ID_PARAM])) {
            $inPostBasketId = (string)$payload[self::INPOST_BASKET_ID_PARAM];
        }

        return $inPostBasketId;
    }

    private function extractMaskedPhoneNumber(array $payload): string
    {
        $maskedPhoneNumber = '';
        if (isset($payload[self::MASKED_PHONE_NUMBER_PARAM]) && is_scalar($payload[self::MASKED_PHONE_NUMBER_PARAM])) {
            $maskedPhoneNumber = (string)$payload[self::MASKED_PHONE_NUMBER_PARAM];
        }

        return $maskedPhoneNumber;
    }

    private function extractPhoneNumber(array $payload): string
    {
        $countryPrefix = '';
        $phoneNumber = '';
        if (isset($payload[self::PHONE_NUMBER_PARAM]) && is_array($payload[self::PHONE_NUMBER_PARAM])) {
            $phoneNumberData = $payload[self::PHONE_NUMBER_PARAM];
            if (isset($phoneNumberData[self::PHONE_PARAM]) && is_string($phoneNumberData[self::PHONE_PARAM])) {
                $phoneNumber = trim((string)$phoneNumberData[self::PHONE_PARAM]);
            }

            if (isset($phoneNumberData[self::COUNTRY_PREFIX_PARAM])
                && is_string($phoneNumberData[self::COUNTRY_PREFIX_PARAM])
            ) {
                $countryPrefix = trim((string)$phoneNumberData[self::COUNTRY_PREFIX_PARAM]);
            }
        }

        return $countryPrefix . $phoneNumber;
    }

    private function extractName(array $payload): string
    {
        $name = '';
        if (isset($payload[self::NAME_PARAM]) && is_scalar($payload[self::NAME_PARAM])) {
            $name = (string)$payload[self::NAME_PARAM];
        }

        return $name;
    }

    private function extractSurname(array $payload): string
    {
        $surname = '';
        if (isset($payload[self::SURNAME_PARAM]) && is_scalar($payload[self::SURNAME_PARAM])) {
            $surname = (string)$payload[self::SURNAME_PARAM];
        }

        return $surname;
    }

    private function extractBrowserId(array $payload): string
    {
        $browserId = '';
        if (isset($payload[self::BROWSER_PARAM]) && is_array($payload[self::BROWSER_PARAM])) {
            $browser = $payload[self::BROWSER_PARAM];
            if (isset($browser[self::BROWSER_ID_PARAM]) && is_string($browser[self::BROWSER_ID_PARAM])) {
                $browserId = (string)$browser[self::BROWSER_ID_PARAM];
            }
        }

        return $browserId;
    }

    private function extractBrowserTrusted(array $payload): bool
    {
        $browserTrusted = false;
        if (isset($payload[self::BROWSER_PARAM]) && is_array($payload[self::BROWSER_PARAM])) {
            $browser = $payload[self::BROWSER_PARAM];
            if (isset($browser[self::BROWSER_TRUSTED_PARAM]) && is_bool($browser[self::BROWSER_TRUSTED_PARAM])) {
                $browserTrusted = (bool)$browser[self::BROWSER_TRUSTED_PARAM];
            }
        }

        return $browserTrusted;
    }

    private function createRequestDebugLog(string $logPrefix, string $message, array $data = []): void
    {
        $serializedData = ($data) ? $this->base64JsonSerializer->serialize($data) : '';
        $dataLabel = ($logPrefix === self::REQUEST_PREFIX) ? 'Payload' : 'Response';
        $this->logger->debug(sprintf('%s: %s %s: %s', $logPrefix, $message, $dataLabel, $serializedData));
    }
}
