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
            'name' => '',
            'productId' => '',
            'language' => $this->widget->getCurrentLanguageCode(),
            'variant' => $this->widget->getLayoutConfig()['variant'] ?: '',
            'darkMode' => $this->widget->getLayoutConfig()['darkMode'] ? 'true' : '',
            'maxWidth' => $this->widget->getLayoutConfig()['maxWidth'] ?: '',
            'minHeight' => $this->widget->getLayoutConfig()['minHeight'] ?: '',
            'frameStyle' => $this->widget->getLayoutConfig()['frameStyle'] ?: '',
            'count' => $this->widget->getCartItemsCount(),
            'bindingPlace' => DisplayConfigProvider::CHECKOUT_PAGE_BINDING_PLACE_NAME,
            'enabledOnCheckoutPage' => $this->widget->isEnabledOnCheckoutPage(),
            'maskedPhoneNumber' => $this->widget->getMaskedPhoneNumber(),
            'isEnabledMinicart' => $this->widget->isEnabledInMiniCart(),
            'scriptUrl' => $scriptUrl,
            'longPollingTimeForInactiveTab' => $this->widget->getLongPollingTimeForInactiveTab(),
            'isEnabledLongPollingForInactiveTab' => $this->widget->isEnabledLongPollingForInactiveTab(),
        ];

        return $config;
    }
}
