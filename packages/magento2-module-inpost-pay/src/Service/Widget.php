<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\BasketInformationResponseInterface;
use InPost\InPostPay\Api\Data\BasketInformationResponseInterfaceFactory;
use InPost\InPostPay\Api\WidgetInterface;
use Magento\Framework\App\RequestInterface;
use InPost\InPostPay\Service\ApiConnector\BindingBasket;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Quote\Api\CartRepositoryInterface;

class Widget implements WidgetInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly BindingBasket $bindingBasket,
        private readonly Base64Json $base64serializer,
        private readonly BasketInformationResponseInterfaceFactory $basketInformationResponseInterfaceFactory,
        private readonly CartRepositoryInterface $quoteRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getPayData(
        string $cartId,
        string $bindingPlace,
        string $browser,
        ?string $prefix,
        ?string $phoneNumber
    ): BasketInformationResponseInterface
    {
        $this->quoteRepository->getActive($cartId);

        $browser = $this->base64serializer->unserialize($browser);
        $browserArray = [
            "user_agent" => $browser['user_agent'],
            "description" => $browser['description'],
            "platform" => $browser['platform'],
            "architecture" => $browser['architecture'],
            "data_time" => date("Y-m-d\TH:i:s.000\Z"),
            "location" => "-",
            "customer_ip" => $this->request->getClientIp(),
            "port" => $this->request->getServer('SERVER_PORT')
        ];

        $result = $this->bindingBasket->bindBasket(
            (int)$cartId,
            $bindingPlace,
            $browserArray,
            $prefix,
            $phoneNumber
        );

        $response = $this->basketInformationResponseInterfaceFactory->create();
        $response->setData($result);
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$response');
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($response->getData(), true));

        return $response;
    }
}
