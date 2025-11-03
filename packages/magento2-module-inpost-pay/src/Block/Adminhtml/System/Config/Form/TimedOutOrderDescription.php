<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\System\Config\Form;

use InPost\InPostPay\Provider\Config\OrderErrorsHandling\Timeout;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TimedOutOrderDescription extends Field
{
    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return __(
            'Time out order problem might occur if Merchant processes order create request for more than %1 seconds.',
            Timeout::TIMEOUT_THRESHOLD_SECONDS
        )->render() . '<br/>' .
        __('If that happens, the request is completed, order is placed in Merchant Store, ')->render() . '<br/>' .
        __('however in the Mobile InPost Pay App time out has already been displayed ')->render() . '<br/>' .
        __('making that order impossible to be paid by customer.')->render();
    }
}
