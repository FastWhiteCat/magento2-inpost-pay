<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Adminhtml;

use InPost\InPostPay\Block\Adminhtml\Order\OrderViewDeliveryInfo;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class OrderViewBlockToHtmlObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $block = $observer->getEvent()->getData('block');
        $transportObject = $observer->getEvent()->getData('transport');

        if ($block instanceof AbstractOrder
            && $block->getNameInLayout() === 'order_shipping_view'
            && $transportObject instanceof DataObject
        ) {
            $html = $transportObject->getData('html');
            $infoBlock = $block->getLayout()->createBlock(OrderViewDeliveryInfo::class);
            $additionalInfoHtml = $infoBlock->toHtml();
            $transportObject->setData('html', $html . $additionalInfoHtml);
        }
    }
}
