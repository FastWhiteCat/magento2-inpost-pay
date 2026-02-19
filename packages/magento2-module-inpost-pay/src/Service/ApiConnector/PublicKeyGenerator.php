<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\PublicKeyRequest;
use InPost\InPostPay\Model\IziApi\Response\PublicKeyResponse;
use InPost\InPostPay\Model\IziApi\Request\PublicKeyRequestFactory;
use InPost\InPostPay\Service\DataTransfer\PublicKeyResponseDataTransfer;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class PublicKeyGenerator
{
    public function __construct(
        private readonly ConnectorInterface            $connector,
        private readonly PublicKeyRequestFactory       $publicKeyRequestFactory,
        private readonly PublicKeyResponseDataTransfer $publicKeyResponseDataTransfer,
        private readonly LoggerInterface               $logger
    ) {
    }

    /**
     * @param string $publicKeyVersion
     * @return PublicKeyResponse
     * @throws LocalizedException
     */
    public function generate(string $publicKeyVersion = ''): PublicKeyResponse
    {
        /** @var PublicKeyRequest $request */
        $request = $this->publicKeyRequestFactory->create();
        if ($publicKeyVersion) {
            $request->setParams([PublicKeyRequest::VERSION => $publicKeyVersion]);
        }

        try {
            $result = $this->connector->sendRequest($request);

            return $this->handle($result);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with public key generation request. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    private function handle(array $result): PublicKeyResponse
    {
        return $this->publicKeyResponseDataTransfer->convertToResponseObject($result);
    }
}
