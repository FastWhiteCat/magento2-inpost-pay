<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Bestsellers\Edit;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData(): array
    {
        $data = [];
        if ($this->getRuleId()) {
            $deleteUrl = $this->getUrl(
                '*/*/delete',
                [InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID => $this->getRuleId()]
            );
            $data = [
                'label' => __('Delete Bestseller Product'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure?') . '\', \'' . $deleteUrl . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
