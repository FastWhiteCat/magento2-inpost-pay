<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Authorization;

use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Model\Registry\SwaggerRegistry;
use InPost\InPostPay\Traits\AnonymizerTrait;
use Magento\Framework\Authorization\PolicyInterface;
use InPost\InPostPay\Api\Validator\RefundWebhookSignatureValidatorInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use InPost\InPostPay\Provider\Config\DebugConfigProvider;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class RefundEventSignatureValidationPolicyPlugin
{
    use AnonymizerTrait;

    public const INPOST_PAY_SIGNATURE_VALIDATED_RESOURCE = 'inpost_pay_refunds_event_signature_validated_resource';
    public const X_SIGNATURE_HEADER = 'X-Signature';
    public const X_API_VERSION_HEADER = 'X-API-Version';
    public const REQUEST_BODY = 'request_body';

    public function __construct(
        private readonly SwaggerRegistry $swaggerRegistry,
        private readonly RestRequest $restRequest,
        private readonly RefundWebhookSignatureValidatorInterface $refundWebhookSignatureValidator,
        private readonly DebugConfigProvider $debugConfigProvider,
        private readonly Json $jsonSerializer,
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
        $endpoint = $this->restRequest->getRequestUri();
        $requestSignature = (string)$this->restRequest->getHeader(self::X_SIGNATURE_HEADER, '');
        $requestApiVersion = (string)$this->restRequest->getHeader(self::X_API_VERSION_HEADER, '');
        $requestBody = (string)$this->restRequest->getContent();
        $requestParams = [];

        if ($this->isJson($requestBody)) {
            $requestParams = $this->jsonSerializer->unserialize($requestBody);
        }

        $requestData = [
            self::X_SIGNATURE_HEADER => $requestSignature,
            self::X_API_VERSION_HEADER => $requestApiVersion,
            self::REQUEST_BODY=> $requestBody
        ];

        $this->logRequest($endpoint, $requestData);

        try {
            $this->refundWebhookSignatureValidator->validate(
                $requestSignature,
                $requestApiVersion,
                $requestParams
            );
        } catch (AuthorizationException $e) {
            $this->logRequest($endpoint, $requestData, $e->getMessage());

            throw new InPostPayAuthorizationException();
        }

        return true;
    }

    private function logRequest(string $endpoint, array $requestData, string $errorMsg = ''): void
    {
        if (!$this->canDebug()) {
            $requestData = [];
        }

        $logMessage = sprintf('Endpoint: %s', $endpoint);
        if (!empty($errorMsg)) {
            $logMessage = sprintf('%s. Error: %s', $logMessage, $errorMsg);
            $this->logger->error($logMessage, $requestData);
        } else {
            $this->logger->debug($logMessage, $requestData);
        }
    }

    private function canDebug(): bool
    {
        return $this->debugConfigProvider->getMinLogLevel() <= Logger::DEBUG;
    }

    private function  isJson(string $string): bool
    {
        $data = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return is_array($data);
    }
}
