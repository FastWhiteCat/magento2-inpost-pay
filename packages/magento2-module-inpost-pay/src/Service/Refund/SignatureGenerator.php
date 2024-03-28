<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Refund;

use Exception;
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

    public function generate(
        string $xCommandId,
        string $transactionId,
        array $requestData = []
    ): string {
        try {
            $merchantSecret = $this->authConfigProvider->getMerchantSecret();
            $extRefundId = $requestData['external_refund_id'] ?? '';
            $refundAmount = $requestData['refund_amount'] ?? null;
            $additionalBusinessData = $requestData['additional_business_data']['additional_data'] ?? '';
            $additionalData = $this->getPreparedAdditionalData(
                is_scalar($additionalBusinessData) ? (string)$additionalBusinessData : ''
            );

            $intermediateSignature = sprintf(
                '%s%s%s%s%s%s',
                $xCommandId,
                $transactionId,
                $additionalData,
                $extRefundId,
                $refundAmount,
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

    private function getPreparedAdditionalData(string $additionalData): ?string
    {
        $preparedData = $additionalData ? json_decode($additionalData, true) : null;

        if (empty($preparedData) || !is_array($preparedData)) {
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
}
