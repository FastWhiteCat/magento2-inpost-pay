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
        $additionalInformation = $this->getInfo()->getAdditionalInformation();
        if (is_array($additionalInformation) && isset($additionalInformation['method_title'])) {
            $methodTitle = $additionalInformation['method_title'];
            if ($methodTitle) {
                $transport->setData((string)__('Payment Type'), __($methodTitle));
            }
        }
        return $transport;
    }
}
