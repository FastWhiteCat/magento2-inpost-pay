<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector;

use InPost\InPostPay\Exception\InPostPayInternalException;
use Magento\Framework\Exception\LocalizedException;

interface RequestInterface
{
    public const CONTENT_TYPE = 'Content-Type';
    public const AUTHORIZATION = 'Authorization';
    public const BEARER_PATTERN = ' Bearer %s';

    public function getUri(): string;

    /**
     * @return string
     * InPostPayInvalidConfigurationException
     */
    public function getApiUrl(): string;

    public function getMethod(): string;

    public function getContentType(): ?string;

    /**
     * @return string|null
     * @throws InPostPayInternalException
     * @throws LocalizedException
     */
    public function getBearerToken(): ?string;

    public function setParams(array $params): void;

    public function getParams(): array;
}
