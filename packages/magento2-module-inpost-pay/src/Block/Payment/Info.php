<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Payment;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @inheritDoc
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        $additionalInformation = $this->getInfo()->getAdditionalInformation();
        $methodTitle = $additionalInformation['method_title'] ?? '';
        if ($methodTitle) {
            $data[(string)__('Payment Type')] = __($methodTitle);
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
