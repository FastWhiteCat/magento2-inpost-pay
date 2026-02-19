<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Validator;

use Magento\Framework\Exception\AuthorizationException;

interface SignatureValidatorInterface
{
    /**
     * @param string $requestSignature
     * @param string $requestSignatureTimestamp
     * @param string $requestPublicKeyVersion
     * @param string $requestPublicKeyHash
     * @param string $requestBody
     * @return true on successful request signature validation
     * @throws AuthorizationException
     */
    public function validate(
        string $requestSignature,
        string $requestSignatureTimestamp,
        string $requestPublicKeyVersion,
        string $requestPublicKeyHash,
        string $requestBody
    ): bool;
}
