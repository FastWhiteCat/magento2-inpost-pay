<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\Widget\Basket\GetPayDataInterface;
use InPost\InPostPay\Service\ApiConnector\BindingBasket;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class GetPayData implements GetPayDataInterface
{
    public const DEFAULT_DATE_FORMAT = "Y-m-d\TH:i:s.000\Z";

    public function __construct(
        private readonly Request $request,
        private readonly BindingBasket $bindingBasket,
        private readonly Base64Json $base64serializer,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly TimezoneInterface $localeDate,
    ) {
    }

    public function execute(
        string $cartId,
        string $bindingPlace,
        string $browser,
        ?string $prefix = null,
        ?string $phoneNumber = null
    ): array {
        $this->quoteRepository->getActive((int)$cartId);

        $browser = $this->base64serializer->unserialize($browser);
        $browserArray = [
            "user_agent" => $browser['user_agent'] ?? '',
            "description" => $browser['description'] ?? '',
            "platform" => $browser['platform'] ?? '',
            "architecture" => $browser['architecture'] ?? '',
            "data_time" => $this->localeDate->date()->format(self::DEFAULT_DATE_FORMAT),
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

        return [$result->getData()];
    }
}
