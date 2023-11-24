<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\Validator\SignatureValidatorInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Psr\Log\LoggerInterface;

class MerchantEndpoint
{
    public const X_SIGNATURE_HEADER = 'x-signature';
    public const X_SIGNATURE_TIMESTAMP_HEADER = 'x-signature-timestamp';
    public const X_SIGNATURE_PUBLIC_KEY_VERSION_HEADER = 'x-public-key-ver';
    public const X_SIGNATURE_PUBLIC_KEY_HASH_HEADER = 'x-public-key-hash';
    public const REQUEST_BODY = 'request_body';

    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly SignatureValidatorInterface $signatureValidator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     * @throws AuthorizationException
     */
    protected function validateRequest(): void
    {
        $endpoint = $this->restRequest->getRequestUri();
        $requestSignature = $this->restRequest->getHeader(self::X_SIGNATURE_HEADER, '');
        $requestSignature = is_scalar($requestSignature) ? (string)$requestSignature : '';
        $requestSignatureTimestamp = $this->restRequest->getHeader(self::X_SIGNATURE_TIMESTAMP_HEADER, '');
        $requestSignatureTimestamp = is_scalar($requestSignatureTimestamp) ? (string)$requestSignatureTimestamp : '';
        $requestPublicKeyVersion = $this->restRequest->getHeader(self::X_SIGNATURE_PUBLIC_KEY_VERSION_HEADER, '');
        $requestPublicKeyVersion = is_scalar($requestPublicKeyVersion) ? (string)$requestPublicKeyVersion : '';
        $requestPublicKeyHash = $this->restRequest->getHeader(self::X_SIGNATURE_PUBLIC_KEY_HASH_HEADER, '');
        $requestPublicKeyHash = is_scalar($requestPublicKeyHash) ? (string)$requestPublicKeyHash : '';
        $requestBody = is_scalar($this->restRequest->getContent()) ? (string)$this->restRequest->getContent() : '';

        $requestData = [
            self::X_SIGNATURE_HEADER => $requestSignature,
            self::X_SIGNATURE_TIMESTAMP_HEADER=> $requestSignatureTimestamp,
            self::X_SIGNATURE_PUBLIC_KEY_VERSION_HEADER=> $requestPublicKeyVersion,
            self::X_SIGNATURE_PUBLIC_KEY_HASH_HEADER=> $requestPublicKeyHash,
            self::REQUEST_BODY=> $requestBody
        ];

        $this->logRequest($endpoint, $requestData);

        try {
            $this->signatureValidator->validate(
                $requestSignature,
                $requestSignatureTimestamp,
                $requestPublicKeyVersion,
                $requestPublicKeyHash,
                $requestBody
            );
        } catch (AuthorizationException $e) {
            $this->logRequest($endpoint, $requestData, $e->getMessage());

            throw new AuthorizationException(__('Signature validation failed!'));
        }
    }

    private function logRequest(string $endpoint, array $requestData, string $errorMsg = ''): void
    {
        $logMessage = sprintf('Endpoint: %s', $endpoint);
        if (!empty($errorMsg)) {
            $logMessage = sprintf('%s. Error: %s', $logMessage, $errorMsg);
            $this->logger->error($logMessage, $requestData);
        } else {
            $this->logger->debug($logMessage, $requestData);
        }
    }
}
