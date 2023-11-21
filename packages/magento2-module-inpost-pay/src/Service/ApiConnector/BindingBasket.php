<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\PublicKeyRequest;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingVerifyRequestFactory;
use InPost\InPostPay\Service\GetBasketId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Psr\Log\LoggerInterface;

class BindingBasket
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BasketBindingRequestFactory $basketBindingRequestFactory,
        private readonly BasketBindingVerifyRequestFactory $basketBindingVerifyRequest,
        private readonly GetBasketId $getBasketId,
        private readonly Base64Json $base64serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $quoteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkBinding(int $quoteId): array
    {
        $basketId = $this->getBasketId->get($quoteId);

        if (!$basketId) {
            return ['browser_trusted' => false, 'basket_linked' => false];
        }

        /** @var PublicKeyRequest $request */
        $request = $this->basketBindingVerifyRequest->create();

        $request->setParams([
            'basket_id' => $basketId,
        ]);

        try {
            return $this->connector->sendRequest($request);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding checking. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    public function bindBasket(
        $quoteId,
        $bindingPlace,
        $browser,
        $prefix = null,
        $phoneNumber = null
    ): array {
        $basketId = $this->getBasketId->get($quoteId, true);

        /** @var PublicKeyRequest $request */
        $request = $this->basketBindingRequestFactory->create();
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$browser');
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($browser, true));

        $browser = $this->base64serializer->unserialize($browser);
        if ($prefix && $phoneNumber) {
            $bindingMethod = 'PHONE';
        } else {
            $bindingMethod = 'DEEP_LINK';
        }

        $request->setParams([
            'basket_id' => $basketId,
            "binding_method" => "DEEP_LINK",
            "binding_place" => "PRODUCT_CARD",
            "browser" =>
//                $browser
                [
                "user_agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36",
                "description" => "Chrome",
                "platform" => "macOS",
                "architecture" => "5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36",
                "data_time" => "2023-11-20T15:55:38.581Z",
                "location" => "-",
                "customer_ip" => "000.000.000.00",
                "port" => "443"
            ],
        ]);

        try {
            $result = $this->connector->sendRequest($request);
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$result');
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($result, true));
            return $result;
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding basket. Details: %1', $e->getMessage());
//            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$errorMsg->render()');
//            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

}
