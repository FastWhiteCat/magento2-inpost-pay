<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\PublicKeyRequest;
use InPost\InPostPay\Model\IziApi\Response\PublicKeyResponse;
use InPost\InPostPay\Model\IziApi\Request\BindingBasketRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\BindingBasketGetRequestFactory;
use InPost\InPostPay\Service\Converter\PublicKeyResponseDataConverter;
use Laminas\Validator\Date;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class BindingBasket
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BindingBasketRequestFactory $bindingBasketRequestFactory,
        private readonly PublicKeyResponseDataConverter $publicKeyResponseDataConverter,
        private readonly BindingBasketGetRequestFactory $bindingBasketGetRequestFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $publicKeyVersion
     * @return array
     * @throws LocalizedException
     */
    public function checkBinding(): array
    {
        /** @var PublicKeyRequest $request */
        $request = $this->bindingBasketGetRequestFactory->create();

        $request->setParams([
            'basket_id' => '1',
        ]);


        try {
            $result = $this->connector->sendRequest($request);

            return $result;
//            return [];
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with public key generation request. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

//    private function handle(array $result): PublicKeyResponse
//    {
////        return $this->publicKeyResponseDataConverter->convertToResponseObject($result);
//    }
}
