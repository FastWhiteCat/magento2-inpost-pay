<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Resolver;

use Magento\Checkout\Model\Session;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use InPost\InPostPay\Service\ApiConnector\BindingBasket;

class Test implements ResolverInterface
{

    public function __construct(
        private readonly BindingBasket $bindingBasket,
        private readonly Session $session
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
//        $cartId = $this->session->getQuoteId();
//        $this->bindingBasket->checkBinding($cartId);
        $this->testBinding();


//        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('testowy');
//        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(print_r($test, true));

        return [];
    }


    public function testBinding() {
        $cartId = 8;
        $browser64 = 'eyJ1c2VyX2FnZW50IjoiTW96aWxsYS81LjAgKE1hY2ludG9zaDsgSW50ZWwgTWFjIE9TIFggMTBfMTVfNykgQXBwbGVXZWJLaXQvNTM3LjM2IChLSFRNTCwgbGlrZSBHZWNrbykgQ2hyb21lLzExNi4wLjAuMCBTYWZhcmkvNTM3LjM2IiwiZGVzY3JpcHRpb24iOiJDaHJvbWUiLCJwbGF0Zm9ybSI6Im1hY09TIiwiYXJjaGl0ZWN0dXJlIjoiNS4wIChNYWNpbnRvc2g7IEludGVsIE1hYyBPUyBYIDEwXzE1XzcpIEFwcGxlV2ViS2l0LzUzNy4zNiAoS0hUTUwsIGxpa2UgR2Vja28pIENocm9tZS8xMTYuMC4wLjAgU2FmYXJpLzUzNy4zNiJ9';
        $bindingPlace = 'PRODUCT_CARD';
        $test = $this->bindingBasket->bindBasket($cartId, $bindingPlace, $browser64);

    }

}
