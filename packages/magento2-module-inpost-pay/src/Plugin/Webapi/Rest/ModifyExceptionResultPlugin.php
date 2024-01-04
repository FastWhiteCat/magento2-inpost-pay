<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest;

use InPost\InPostPay\Exception\InPostPayException;
use Magento\Framework\Webapi\Rest\Response\Renderer\Json as Subject;
use Psr\Log\LoggerInterface;

class ModifyExceptionResultPlugin
{
    public const INPOST_EXCEPTION_RESULT_ERROR_CODE = 'error_code';
    public const INPOST_EXCEPTION_RESULT_ERROR_MESSAGE = 'error_message';

    private array $customizableErrorCodes = [
        'UNAUTHORIZED',
        'INTERNAL_SERVER_ERROR',
        'BAD_REQUEST',
        'BASKET_NOT_FOUND',
        'ORDER_NOT_CREATE',
        'ORDER_NOT_UPDATE',
        'ORDER_NOT_FOUND'
    ];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Subject $subject
     * @param array $data
     * @return array[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRender(Subject $subject, array $data): array
    {
        $errorMessage = $this->extractErrorMessage($data);
        $errorParams = $this->extractErrorParams($data);
        $errorCode = $this->extractErrorCodeFromParams($errorParams);

        if ($errorCode && $errorMessage && in_array($errorCode, $this->customizableErrorCodes)) {
            $data = [
                self::INPOST_EXCEPTION_RESULT_ERROR_CODE => $errorCode,
                self::INPOST_EXCEPTION_RESULT_ERROR_MESSAGE => $errorMessage
            ];

            $this->logger->error('INCOMING: Exception Response', $data);
        }

        return [$data];
    }

    private function extractErrorMessage(array $data): ?string
    {
        return (isset($data['message']) && is_scalar($data['message'])) ? (string)$data['message'] : null;
    }

    private function extractErrorParams(array $data): array
    {
        return (isset($data['parameters']) && is_array($data['parameters'])) ? $data['parameters'] : [];
    }

    private function extractErrorCodeFromParams(array $errorParams): ?string
    {
        $errorCode = null;
        if (isset($errorParams[InPostPayException::INPOST_ERROR_CODE])
            && is_scalar($errorParams[InPostPayException::INPOST_ERROR_CODE])
        ) {
            $errorCode = (string)$errorParams[InPostPayException::INPOST_ERROR_CODE];
        }

        return $errorCode;
    }
}
