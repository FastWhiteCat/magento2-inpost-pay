<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Bestsellers\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData(): array
    {
        return [
            'label' => __('Save Bestseller Product'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
