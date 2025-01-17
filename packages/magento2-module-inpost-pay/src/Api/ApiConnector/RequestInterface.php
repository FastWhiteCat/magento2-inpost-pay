<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector;

use InPost\InPostPay\Exception\InPostPayInternalException;
use Magento\Framework\Exception\LocalizedException;

interface RequestInterface
{
    public const CONTENT_TYPE = 'Content-Type';
    public const AUTHORIZATION = 'Authorization';
    public const PLUGIN_VERSION = 'inpay-plugin-version';
    public const BEARER_PATTERN = ' Bearer %s';

    public function getUri(bool $keepParamsIntact = false): string;

    /**
     * @return string
     * InPostPayInvalidConfigurationException
     */
    public function getApiUrl(): string;

    public function getHeaders(bool $keepParamsIntact = false): array;

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
