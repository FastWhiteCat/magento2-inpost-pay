<?php

declare(strict_types=1);

namespace InPost\Restrictions\Block\Adminhtml\RestrictionsRule\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData(): array
    {
        $data = [];
        if ($this->getRuleId()) {
            $deleteUrl = $this->getUrl('*/*/delete', ['rule_id' => $this->getRuleId()]);
            $data = [
                'label' => __('Delete Rule'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure?') . '\', \'' . $deleteUrl . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
