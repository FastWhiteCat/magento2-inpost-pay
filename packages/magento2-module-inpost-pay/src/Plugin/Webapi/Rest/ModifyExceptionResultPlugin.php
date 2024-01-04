<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest;

use InPost\InPostPay\Exception\InPostPayException;
use Magento\Framework\Webapi\Rest\Response\Renderer\Json as Subject;

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

    public function beforeRender(Subject $subject, array $data): array
    {
        $errorMessage = (isset($data['message']) && is_scalar($data['message'])) ? (string)$data['message'] : null;
        $errorParams = (isset($data['parameters']) && is_array($data['parameters'])) ? $data['parameters'] : [];
        $errorCode = null;
        if (isset($errorParams[InPostPayException::INPOST_ERROR_CODE])
            && is_scalar($errorParams[InPostPayException::INPOST_ERROR_CODE])
        ) {
            $errorCode = (string)$errorParams[InPostPayException::INPOST_ERROR_CODE];
        }

        if ($errorCode && $errorMessage && in_array($errorCode, $this->customizableErrorCodes)) {
            $data = [
                self::INPOST_EXCEPTION_RESULT_ERROR_CODE => $errorCode,
                self::INPOST_EXCEPTION_RESULT_ERROR_MESSAGE => $errorMessage
            ];
        }


        return [$data];
    }
}
