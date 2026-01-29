<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Validator;

use Magento\Framework\Exception\AuthorizationException;

interface RefundWebhookSignatureValidatorInterface
{
    /**
     * @param string $requestSignature
     * @param string $requestApiVersion
     * @param array $requestParams
     * @return true on successful request signature validation
     * @throws AuthorizationException
     */
    public function validate(
        string $requestSignature,
        string $requestApiVersion,
        array $requestParams
    ): bool;
}
