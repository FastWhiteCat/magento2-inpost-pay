<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketDeleteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Base64Json as Base64JsonSerializer;
use Psr\Log\LoggerInterface;

class BasketDelete implements BasketDeleteInterface
{
    private const REQUEST_PREFIX = 'BASKET_DELETE_REQUEST';
    private const RESPONSE_PREFIX = 'BASKET_DELETE_RESPONSE';

    public function __construct(
        private readonly Base64JsonSerializer $base64JsonSerializer,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute(string $basketId): void
    {
        $logMessage = sprintf('Delete Basket ID: %s', $basketId);
        $this->createRequestDebugLog(self::REQUEST_PREFIX, $logMessage);

        try {
            $this->inPostPayQuoteRepository->delete($this->inPostPayQuoteRepository->getByInPostBasketId($basketId));
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('An error occurred during delete process. Check error logs'));
        }

        $this->createRequestDebugLog(self::RESPONSE_PREFIX, $logMessage);
    }

    private function createRequestDebugLog(string $logPrefix, string $message, array $data = []): void
    {
        $serializedData = ($data) ? $this->base64JsonSerializer->serialize($data) : '';
        $dataLabel = ($logPrefix === self::REQUEST_PREFIX) ? 'Payload' : 'Response';
        $this->logger->debug(sprintf('%s: %s %s: %s', $logPrefix, $message, $dataLabel, $serializedData));
    }
}
