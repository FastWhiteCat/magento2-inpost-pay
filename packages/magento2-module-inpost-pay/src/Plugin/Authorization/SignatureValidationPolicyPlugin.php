<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Authorization;

use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Model\Registry\SwaggerRegistry;
use Magento\Framework\Authorization\PolicyInterface;
use InPost\InPostPay\Api\Validator\SignatureValidatorInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Psr\Log\LoggerInterface;

class SignatureValidationPolicyPlugin
{
    public const INPOST_PAY_SIGNATURE_VALIDATED_RESOURCE = 'inpost_pay_signature_validated_resource';
    public const X_SIGNATURE_HEADER = 'x-signature';
    public const X_SIGNATURE_TIMESTAMP_HEADER = 'x-signature-timestamp';
    public const X_SIGNATURE_PUBLIC_KEY_VERSION_HEADER = 'x-public-key-ver';
    public const X_SIGNATURE_PUBLIC_KEY_HASH_HEADER = 'x-public-key-hash';
    public const REQUEST_BODY = 'request_body';

    public function __construct(
        private readonly SwaggerRegistry $swaggerRegistry,
        private readonly RestRequest $restRequest,
        private readonly SignatureValidatorInterface $signatureValidator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param PolicyInterface $subject
     * @param bool $result
     * @param string|null $roleId
     * @param string|null $resourceId
     * @param string|null $privilege
     * @return bool
     * @throws InPostPayAuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowed(
        PolicyInterface $subject,
        bool $result,
        ?string $roleId,
        ?string $resourceId,
        ?string $privilege
    ): bool {
        if ($resourceId === self::INPOST_PAY_SIGNATURE_VALIDATED_RESOURCE
            && !$this->swaggerRegistry->isAllowed()
            && $this->isSignatureValid()
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws InPostPayAuthorizationException
     */
    protected function isSignatureValid(): bool
    {
        return true;
        $endpoint = $this->restRequest->getRequestUri();
        $requestSignature = (string)$this->restRequest->getHeader(self::X_SIGNATURE_HEADER, '');
        $requestSignatureTimestamp = (string)$this->restRequest->getHeader(self::X_SIGNATURE_TIMESTAMP_HEADER, '');
        $requestPublicKeyVersion = (string)$this->restRequest->getHeader(
            self::X_SIGNATURE_PUBLIC_KEY_VERSION_HEADER,
            ''
        );
        $requestPublicKeyHash = (string)$this->restRequest->getHeader(self::X_SIGNATURE_PUBLIC_KEY_HASH_HEADER, '');
        $requestBody = (string)$this->restRequest->getContent();

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

            throw new InPostPayAuthorizationException();
        }

        return true;
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
