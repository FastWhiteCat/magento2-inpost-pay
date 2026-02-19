<?php

namespace InPost\InPostPay\Model;

use InPost\InPostPay\ViewModel\Widget;
use Magento\Checkout\Model\ConfigProviderInterface;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;

class InPostPayConfigurationModel implements ConfigProviderInterface
{
    public function __construct(
        private readonly Widget $widget,
    ) {
    }

    public function getConfig()
    {

        $config = [];

        $scriptUrl = $this->widget->getScriptUrl(DisplayConfigProvider::CHECKOUT_PAGE_BINDING_PLACE_NAME);

        $config['inPostConfig'] = [
            'language' => $this->widget->getCurrentLanguageCode(),
            'variation' => $this->widget->getLayoutConfig(),
            'bindingPlace' => DisplayConfigProvider::CHECKOUT_PAGE_BINDING_PLACE_NAME,
            'enabledOnCheckoutPage' => $this->widget->isEnabledOnCheckoutPage(),
            'scriptUrl' => $scriptUrl,
            'merchantClientId' => $this->widget->getClientMerchantId()
        ];

        return $config;
    }
}
