<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector;

use Magento\Framework\Exception\LocalizedException;

interface ConnectorInterface
{
    public const REQUEST = 'request';
    public const RESPONSE = 'response';
    public const HEADERS = 'headers';

    /**
     * @param RequestInterface $request
     * @return array
     * @throws LocalizedException
     */
    public function sendRequest(RequestInterface $request): array;
}
