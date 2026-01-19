<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use InPost\InPostPay\Api\Validator\RefundWebhookSignatureValidatorInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use Magento\Framework\Exception\AuthorizationException;
use Laminas\Http\Response;
use Psr\Log\LoggerInterface;

class RefundWebhookSignatureValidator implements RefundWebhookSignatureValidatorInterface
{
    private array $signatureFields = [
        'eventData.amount.currency',
        'eventData.amount.valueInCents',
        'eventData.createdDate',
        'eventData.eventDateTime',
        'eventData.merchantId',
        'eventData.operationId',
        'eventData.payment.id',
        'eventData.payment.method',
        'eventData.refundReference',
        'eventData.status',
        'eventType',
    ];

    /**
     * @param AuthConfigProvider $authConfigProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly AuthConfigProvider $authConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $requestSignature
     * @param string $requestApiVersion
     * @param array $requestParams
     * @return bool
     * @throws AuthorizationException
     * @throws InPostPayInternalException
     */
    public function validate(string $requestSignature, string $requestApiVersion, array $requestParams): bool
    {
        try {
            $merchantSecret = $this->authConfigProvider->getMerchantSecret();
            $expectedSignature = $this->prepareExpectedSignature($requestApiVersion, $requestParams, $merchantSecret);

            if (strtolower($requestSignature) !== $expectedSignature) {
                $validationErrorMsg = __('Incorrect refund webhook signature!');

                throw new AuthorizationException($validationErrorMsg, null, Response::STATUS_CODE_401);
            }
        } catch (AuthorizationException $e) {
            $this->logger->error(
                sprintf('Refund Webhook Signature validation process failed. Reason: %s', $e->getMessage())
            );

            throw $e;
        }

        return true;
    }

    /**
     * @param array $source
     * @param string $path
     * @return string|null
     */
    private function getValueByPath(array $source, string $path): ?string
    {
        $segments = explode('.', $path);
        $current = $source;

        foreach ($segments as $segment) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                $current = $current[$segment];
            } else {
                return null;
            }
        }

        return is_scalar($current) ? (string)$current : null;
    }

    private function prepareExpectedSignature(
        string $requestApiVersion,
        array $requestParams,
        string $merchantSecret
    ): string {
        $values = [];

        foreach ($this->signatureFields as $path) {
            $value = $this->getValueByPath($requestParams, $path);
            $values[] = $value === null ? '' : $value;
        }

        $message = $requestApiVersion . implode('', $values) . $merchantSecret;
        $expectedSignature = hash('sha512', $message);

        return strtolower($expectedSignature);
    }
}
