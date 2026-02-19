<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\CheckoutAgreement\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData(): array
    {
        $data = [];
        if ($this->getAgreementId()) {
            $deleteUrl = $this->getUrl('*/*/delete', ['agreement_id' => $this->getAgreementId()]);
            $data = [
                'label' => __('Delete InPost Pay Checkout Agreement'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure?') . '\', \'' . $deleteUrl . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
