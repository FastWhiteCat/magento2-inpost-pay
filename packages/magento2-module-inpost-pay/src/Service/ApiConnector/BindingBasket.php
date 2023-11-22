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
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;

class BindingBasket
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BasketBindingRequestFactory $basketBindingRequestFactory,
        private readonly BasketBindingVerifyRequestFactory $basketBindingVerifyRequest,
        private readonly GetBasketId $getBasketId,
        private readonly CookieManagerInterface $cookieManager,
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
        int $quoteId,
        string $bindingPlace,
        array $browser,
        ?string $prefix = null,
        ?string $phoneNumber = null
    ): array {
        $basketId = $this->getBasketId->get($quoteId, true);

        /** @var PublicKeyRequest $request */
        $request = $this->basketBindingRequestFactory->create();
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$browser');
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($browser, true));

        $phoneNumberArray = [];
        if ($prefix && $phoneNumber) {
            $phoneNumberArray = [
                'country_prefix' => $prefix,
                'phone' => $phoneNumber,
                ];
            $bindingMethod = 'PHONE';
        } else {
            $bindingMethod = 'DEEP_LINK';
        }

        $params = [
            'basket_id' => $basketId,
            "binding_method" => $bindingMethod,
            "binding_place" => $bindingPlace,
            "phone_number" => $phoneNumberArray,
            "browser" => $browser
        ];
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$params');
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($params,true));
        $request->setParams($params);

        try {
            $result = $this->connector->sendRequest($request);
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$result');
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($result, true));
            $result['basket_id'] = $basketId;
            return $result;
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding basket. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
