<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Refund;

use Exception;
use InPost\InPostPay\Api\Data\Merchant\RefundInterface;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class SignatureGenerator
{
    public const DEFAULT_ALGORITHM_HASH_NAME = 'sha512';
    public const DEFAULT_ALGORITHM_HASH_PREFIX = 'SHA-512';

    private string $hashAlgorithmName;
    private string $hashAlgorithmPrefix;

    public function __construct(
        private readonly AuthConfigProvider $authConfigProvider,
        private readonly LoggerInterface $logger,
        string $hashAlgorithmName,
        string $hashAlgorithmPrefix,
    ) {
        $this->hashAlgorithmName = $hashAlgorithmName;
        $this->hashAlgorithmPrefix = $hashAlgorithmPrefix;
    }

    public function generate(RefundInterface $refund, ?int $storeId = null): string
    {
        try {
            $merchantSecret = $this->authConfigProvider->getMerchantSecret($storeId);
            $refundAdditionalBusinessData = $refund->getAdditionalBusinessData()?->getAdditionalData();
            $preparedAdditionalData = $this->getPreparedAdditionalData($refundAdditionalBusinessData);

            $intermediateSignature = sprintf(
                '%s%s%s%s%s%s',
                $refund->getXCommandId(),
                $refund->getTransactionId(),
                $preparedAdditionalData,
                $refund->getExternalRefundId(),
                $refund->getRefundAmount(),
                $merchantSecret
            );
            $signature = hash($this->hashAlgorithmName, $intermediateSignature);

            return $this->hashAlgorithmPrefix . "_$signature";
        } catch (Exception $e) {
            $errorMsg = __(
                'There was a problem with refund signature generation request. Details: %1',
                $e->getMessage()
            );
            $this->logger->critical($errorMsg->render());
            throw new LocalizedException($errorMsg);
        }
    }

    private function getPreparedAdditionalData(?string $additionalData): ?string
    {
        if (!$additionalData) {
            return null;
        }

        if ($this->isJson($additionalData)) {
            $preparedData = json_decode($additionalData, true);
            if (!is_array($preparedData)) {
                return null;
            }

            return implode('', array_map(
                static function ($key, $value) {
                    return sprintf('%s%s', $key, $value);
                },
                array_keys($preparedData),
                $preparedData
            ));
        }

        return $additionalData;
    }

    private function isJson(string $string): bool
    {
        json_decode($string, true);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
