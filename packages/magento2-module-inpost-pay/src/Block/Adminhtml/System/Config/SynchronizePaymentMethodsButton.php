<?php

namespace InPost\InPostPay\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;

class SynchronizePaymentMethodsButton extends Field
{
    /** @var UrlInterface */
    private $urlBuilder;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('InPost_InPostPay::system/config/synchronizePaymentMethodsButton.phtml');
    }

    public function getButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'inpostpay_synchronize_payment_methods_button',
                'label' => __('Synchronize payment methods'),
            ]
        );

        return $button->toHtml();
    }

    public function getAdminUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'inpostpay/paymentMethods/synchronize',
            ['store' => $this->_request->getParam('store')]
        );
    }

    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }
}
