<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Webapi\Rest\Response;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Provider\MagentoModuleVersionProvider;
use Magento\Framework\Webapi\Rest\Response as HttpResponse;
use Psr\Log\LoggerInterface;

abstract class AbstractVersionHeaderPlugin
{
    protected string $requestPath = '';

    public function __construct(
        private readonly HttpResponse $response,
        private readonly MagentoModuleVersionProvider $versionProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    protected function setVersionHeader(): void
    {
        $this->response->setHeader(
            RequestInterface::PLUGIN_VERSION,
            $this->versionProvider->getVersion(),
            true
        );
    }

    protected function getRequestPath(): string
    {
        return $this->requestPath;
    }

    protected function logVersionHeader(): void
    {
        $this->logger->debug(
            sprintf(
                'Added response header "%s: %s" for endpoint %s',
                RequestInterface::PLUGIN_VERSION,
                $this->versionProvider->getVersion(),
                $this->requestPath
            )
        );
    }
}
